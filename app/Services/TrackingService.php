<?php

namespace App\Services;

use App\Models\InboundShipment;
use Carbon\Carbon;

class TrackingService
{
    /**
     * Get dashboard map data with predictive positions.
     */
    public function getDashboardMapData(int $companyId): array
    {
        // Fetch active shipments linked to POs with Suppliers who have coordinates
        $shipments = InboundShipment::where('company_id', $companyId)
            ->whereIn('status', ['on_water', 'booked', 'customs', 'planned'])
            ->whereNotNull('etd')
            ->whereNotNull('eta')
            ->whereHas('purchaseOrders.supplier', function ($q) {
                $q->whereNotNull('latitude')->whereNotNull('longitude');
            })
            ->with(['purchaseOrders.supplier'])
            ->get();

        $mapData = [];

        // Fixed Destination (Warehouse) - For now, using Jakarta/Surabaya as default if no Warehouse coords
        // Ideally, this should come from the specific warehouse linked to the PO or Shipment
        $destLat = -6.175110; // Jakarta
        $destLng = 106.865036;

        foreach ($shipments as $shipment) {
            $supplier = $shipment->purchaseOrders->first()->supplier;
            
            // Should not happen due to query filter, but safety check
            if (!$supplier || !$supplier->latitude || !$supplier->longitude) continue;

            $origin = [
                'lat' => (float) $supplier->latitude, 
                'lng' => (float) $supplier->longitude
            ];

            $destination = [
                'lat' => $destLat,
                'lng' => $destLng
            ];

            // Calculate Predictive Position
            $position = $this->calculatePosition(
                $origin, 
                $destination, 
                $shipment->etd, 
                $shipment->eta
            );

            $mapData[] = [
                'id' => $shipment->id,
                'number' => $shipment->shipment_number,
                'carrier' => $shipment->carrier_name ?? 'Unknown Carrier',
                'origin' => $origin,
                'destination' => $destination,
                'position' => $position, // Current calculated position
                'supplier_name' => $supplier->name,
                'etd' => $shipment->etd->format('d M'),
                'eta' => $shipment->eta->format('d M'),
                'progress' => $this->calculateProgress($shipment->etd, $shipment->eta) * 100,
                'status' => $this->humanStatus($shipment->status)
            ];
        }

        return $mapData;
    }

    /**
     * Calculate current position using Linear Interpolation (LERP)
     */
    public function calculatePosition(array $origin, array $destination, Carbon $etd, Carbon $eta): array
    {
        $progress = $this->calculateProgress($etd, $eta);

        // Map LERP formula: P(t) = P0 + t * (P1 - P0)
        $lat = $origin['lat'] + ($progress * ($destination['lat'] - $origin['lat']));
        $lng = $origin['lng'] + ($progress * ($destination['lng'] - $origin['lng']));

        return ['lat' => $lat, 'lng' => $lng];
    }

    /**
     * Calculate progress percentage (0.0 to 1.0)
     */
    private function calculateProgress(Carbon $etd, Carbon $eta): float
    {
        $now = now();

        if ($now->lessThan($etd)) return 0.0;
        if ($now->greaterThan($eta)) return 1.0;

        $totalDuration = $etd->diffInSeconds($eta);
        if ($totalDuration == 0) return 1.0;

        $elapsed = $etd->diffInSeconds($now);

        return min(1.0, max(0.0, $elapsed / $totalDuration));
    }

    private function humanStatus($status): string 
    {
        return ucwords(str_replace('_', ' ', $status));
    }
}
