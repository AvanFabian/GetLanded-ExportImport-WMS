<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Batch;
use App\Models\StockLocation;
use Illuminate\Support\Collection;

class PickingListService
{
    /**
     * Generate picking list for a sales order
     * 
     * @param SalesOrder $order
     * @param string $strategy 'FIFO' or 'FEFO'
     * @param int|null $sourceWarehouseId Specific warehouse or null for any
     * @return array
     */
    public function generate(SalesOrder $order, string $strategy = 'FIFO', ?int $sourceWarehouseId = null): array
    {
        $pickingList = [];

        foreach ($order->items as $item) {
            $picks = $this->allocateBatchesForItem($item, $strategy, $sourceWarehouseId);
            $pickingList[] = [
                'item' => $item,
                'picks' => $picks,
                'fulfilled' => $picks->sum('pick_quantity') >= $item->quantity,
            ];
        }

        return [
            'order' => $order,
            'picking_list' => $pickingList,
            'complete' => collect($pickingList)->every(fn($p) => $p['fulfilled']),
        ];
    }

    /**
     * Allocate batches for a single order item
     */
    protected function allocateBatchesForItem(
        SalesOrderItem $item, 
        string $strategy, 
        ?int $warehouseId
    ): Collection {
        $remaining = $item->quantity - ($item->shipped_quantity ?? 0);
        
        if ($remaining <= 0) {
            return collect();
        }

        // Get eligible locations with available stock
        $query = StockLocation::query()
            ->whereHas('batch', function($q) use ($item) {
                $q->where('product_id', $item->product_id)
                  ->where('is_quarantined', false)
                  ->where('status', 'active');
            })
            ->where('quantity', '>', 0)
            ->with(['batch', 'bin.zone.warehouse']);

        // Filter by warehouse if specified
        if ($warehouseId) {
            $query->whereHas('bin.zone.warehouse', function($q) use ($warehouseId) {
                $q->where('id', $warehouseId);
            });
        }

        // Apply sorting strategy
        $locations = match($strategy) {
            'FEFO' => $query->get()->sortBy(fn($loc) => $loc->batch->expiry_date ?? PHP_INT_MAX),
            default => $query->get()->sortBy(fn($loc) => $loc->batch->created_at), // FIFO
        };

        $picks = collect();

        foreach ($locations as $location) {
            if ($remaining <= 0) break;

            $available = $location->quantity - ($location->reserved_quantity ?? 0);
            if ($available <= 0) continue;

            $pickQty = min($available, $remaining);
            
            $picks->push([
                'location_id' => $location->id,
                'batch_id' => $location->batch_id,
                'batch_number' => $location->batch->batch_number,
                'expiry_date' => $location->batch->expiry_date,
                'bin_id' => $location->bin_id,
                'bin_code' => $location->bin->full_code ?? 'N/A',
                'warehouse_id' => $location->bin->zone->warehouse_id ?? null,
                'warehouse_name' => $location->bin->zone->warehouse->name ?? 'N/A',
                'available_quantity' => $available,
                'pick_quantity' => $pickQty,
            ]);

            $remaining -= $pickQty;
        }

        return $picks;
    }

    /**
     * Confirm picking list and reserve stock
     */
    public function confirmPicks(array $picks): void
    {
        foreach ($picks as $pick) {
            $location = StockLocation::find($pick['location_id']);
            if ($location) {
                $location->increment('reserved_quantity', $pick['pick_quantity']);
            }
        }
    }

    /**
     * Generate picking list with manual batch override
     */
    public function generateWithOverride(SalesOrder $order, array $manualAllocations): array
    {
        $pickingList = [];

        foreach ($order->items as $item) {
            $itemKey = $item->id;
            
            if (isset($manualAllocations[$itemKey])) {
                // Use manual allocation
                $picks = $this->buildManualPicks($manualAllocations[$itemKey]);
            } else {
                // Fall back to FIFO
                $picks = $this->allocateBatchesForItem($item, 'FIFO', null);
            }
            
            $pickingList[] = [
                'item' => $item,
                'picks' => $picks,
                'fulfilled' => $picks->sum('pick_quantity') >= $item->quantity,
            ];
        }

        return [
            'order' => $order,
            'picking_list' => $pickingList,
            'complete' => collect($pickingList)->every(fn($p) => $p['fulfilled']),
        ];
    }

    protected function buildManualPicks(array $allocations): Collection
    {
        return collect($allocations)->map(function($alloc) {
            $location = StockLocation::with(['batch', 'bin.zone.warehouse'])->find($alloc['location_id']);
            
            return [
                'location_id' => $location->id,
                'batch_id' => $location->batch_id,
                'batch_number' => $location->batch->batch_number,
                'expiry_date' => $location->batch->expiry_date,
                'bin_id' => $location->bin_id,
                'bin_code' => $location->bin->full_code ?? 'N/A',
                'warehouse_id' => $location->bin->zone->warehouse_id ?? null,
                'warehouse_name' => $location->bin->zone->warehouse->name ?? 'N/A',
                'available_quantity' => $location->quantity - ($location->reserved_quantity ?? 0),
                'pick_quantity' => $alloc['quantity'],
            ];
        });
    }

    /**
     * Check quarantine guard - prevent allocation from quarantined batches
     */
    public function validatePicks(Collection $picks): array
    {
        $errors = [];

        foreach ($picks as $pick) {
            $batch = Batch::find($pick['batch_id']);
            
            if ($batch && $batch->is_quarantined) {
                $errors[] = "Batch {$batch->batch_number} is quarantined and cannot be picked.";
            }

            $location = StockLocation::find($pick['location_id']);
            if ($location) {
                $available = $location->quantity - ($location->reserved_quantity ?? 0);
                if ($pick['pick_quantity'] > $available) {
                    $errors[] = "Insufficient stock at location {$pick['bin_code']}. Available: {$available}, Requested: {$pick['pick_quantity']}";
                }
            }
        }

        return $errors;
    }
}
