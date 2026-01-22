<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\StockLocation;
use App\Models\Product;
use Illuminate\Support\Collection;

class InventoryReportService
{
    /**
     * Get inventory aging report
     */
    public function agingReport(int $companyId): Collection
    {
        $batches = Batch::whereHas('product', fn($q) => $q->where('company_id', $companyId))
            ->with(['product', 'stockLocations'])
            ->where('status', 'active')
            ->get();

        return $batches->map(function ($batch) {
            $age = $batch->created_at->diffInDays(now());
            $totalQty = $batch->stockLocations->sum('quantity');
            
            $bracket = match(true) {
                $age <= 30 => '0-30 days',
                $age <= 60 => '31-60 days',
                $age <= 90 => '61-90 days',
                default => '90+ days',
            };

            return [
                'batch_number' => $batch->batch_number,
                'product' => $batch->product->name,
                'age_days' => $age,
                'age_bracket' => $bracket,
                'quantity' => $totalQty,
                'value' => $totalQty * ($batch->unit_purchase_price ?? $batch->cost_price),
                'expiry_date' => $batch->expiry_date,
                'days_to_expiry' => $batch->expiry_date 
                    ? now()->diffInDays($batch->expiry_date, false) 
                    : null,
            ];
        })->sortByDesc('age_days');
    }

    /**
     * Get CBM (Cubic Meter) utilization report
     */
    public function cbmReport(int $companyId): array
    {
        $products = Product::where('company_id', $companyId)
            ->whereNotNull('length_cm')
            ->whereNotNull('width_cm')
            ->whereNotNull('height_cm')
            ->with(['batches.stockLocations'])
            ->get();

        $totalCBM = 0;
        $items = [];

        foreach ($products as $product) {
            $unitCBM = ($product->length_cm * $product->width_cm * $product->height_cm) / 1000000;
            $totalQty = $product->batches->sum(fn($b) => $b->stockLocations->sum('quantity'));
            $productCBM = $unitCBM * $totalQty;
            $totalCBM += $productCBM;

            $items[] = [
                'product' => $product->name,
                'sku' => $product->sku,
                'dimensions' => "{$product->length_cm}x{$product->width_cm}x{$product->height_cm} cm",
                'unit_cbm' => round($unitCBM, 4),
                'quantity' => $totalQty,
                'total_cbm' => round($productCBM, 4),
            ];
        }

        return [
            'items' => $items,
            'total_cbm' => round($totalCBM, 4),
        ];
    }

    /**
     * Get stock reservation summary (Physical/Reserved/Available)
     */
    public function reservationSummary(int $companyId): Collection
    {
        return Product::where('company_id', $companyId)
            ->with(['batches.stockLocations'])
            ->get()
            ->map(function ($product) {
                $physical = 0;
                $reserved = 0;

                foreach ($product->batches as $batch) {
                    if ($batch->status === 'active' && !$batch->is_quarantined) {
                        foreach ($batch->stockLocations as $loc) {
                            $physical += $loc->quantity;
                            $reserved += $loc->reserved_quantity ?? 0;
                        }
                    }
                }

                return [
                    'product' => $product->name,
                    'sku' => $product->sku,
                    'physical' => $physical,
                    'reserved' => $reserved,
                    'available' => $physical - $reserved,
                ];
            });
    }

    /**
     * Business health analytics
     */
    public function businessHealthReport(int $companyId): array
    {
        // Inventory turnover (simplified)
        $totalInventoryValue = Batch::whereHas('product', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'active')
            ->get()
            ->sum(function ($batch) {
                $qty = $batch->stockLocations->sum('quantity');
                return $qty * ($batch->unit_purchase_price ?? $batch->cost_price);
            });

        // Aging breakdown
        $aging = $this->agingReport($companyId);
        $agingBreakdown = $aging->groupBy('age_bracket')->map(fn($g) => [
            'count' => $g->count(),
            'value' => $g->sum('value'),
        ]);

        // Expiring soon
        $expiringSoon = Batch::whereHas('product', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'active')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();

        // Low stock (placeholder - needs min_stock field)
        $lowStock = Product::where('company_id', $companyId)
            ->whereNotNull('min_stock')
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->batches->sum(fn($b) => $b->stockLocations->sum('quantity'));
                return $totalStock < $product->min_stock;
            })
            ->count();

        return [
            'total_inventory_value' => $totalInventoryValue,
            'aging_breakdown' => $agingBreakdown,
            'expiring_soon_count' => $expiringSoon,
            'low_stock_count' => $lowStock,
        ];
    }
}
