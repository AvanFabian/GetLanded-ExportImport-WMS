<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Product;
use App\Models\Batch;
use App\Models\StockLocation;

class SoftInventoryService
{
    /**
     * Check stock availability with soft limit support
     *
     * @param int $productId
     * @param float $requestedQty
     * @param int|null $warehouseId
     * @param int|null $companyId
     * @return array ['allowed' => bool, 'warning' => string|null, 'available' => float, 'shortage' => float]
     */
    public function checkAvailability(
        int $productId,
        float $requestedQty,
        ?int $warehouseId = null,
        ?int $companyId = null
    ): array {
        $companyId = $companyId ?? auth()->user()?->company_id;
        $company = Company::find($companyId);
        $stockLimitMode = $company?->stock_limit_mode ?? 'block';

        // Calculate available stock
        $available = $this->getAvailableStock($productId, $warehouseId);
        $shortage = $requestedQty - $available;

        // Sufficient stock
        if ($shortage <= 0) {
            return [
                'allowed' => true,
                'warning' => null,
                'available' => $available,
                'shortage' => 0,
                'mode' => $stockLimitMode,
            ];
        }

        // Insufficient stock - check policy
        if ($stockLimitMode === 'warning') {
            // Warning mode: Allow but flag it
            return [
                'allowed' => true,
                'warning' => "Insufficient stock: shortage of " . number_format($shortage, 2) . " units. Pre-selling enabled.",
                'available' => $available,
                'shortage' => $shortage,
                'mode' => 'warning',
                'overselling' => true,
            ];
        }

        // Block mode: Deny the transaction
        return [
            'allowed' => false,
            'warning' => null,
            'error' => "Insufficient stock. Available: " . number_format($available, 2) . ", Requested: " . number_format($requestedQty, 2),
            'available' => $available,
            'shortage' => $shortage,
            'mode' => 'block',
        ];
    }

    /**
     * Get available stock for a product (excluding reserved and quarantined)
     */
    public function getAvailableStock(int $productId, ?int $warehouseId = null): float
    {
        $query = StockLocation::whereHas('batch', function ($q) use ($productId) {
            $q->where('product_id', $productId)
              ->where('is_quarantined', false)
              ->where('status', 'active');
        })
        ->selectRaw('SUM(quantity - reserved_quantity) as available');

        if ($warehouseId) {
            $query->whereHas('bin.rack.zone', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        return $query->value('available') ?? 0;
    }

    /**
     * Get expected incoming stock (from pending purchase orders or transfers)
     */
    public function getIncomingStock(int $productId, ?int $warehouseId = null): float
    {
        // From pending purchase orders (not yet received)
        $fromPO = \App\Models\PurchaseOrderItem::whereHas('purchaseOrder', function ($q) {
            $q->whereIn('status', ['approved', 'partial_received']);
        })
        ->where('product_id', $productId)
        ->selectRaw('SUM(quantity - received_quantity) as incoming')
        ->value('incoming') ?? 0;

        // From in-transit stock transfers
        $fromTransfer = \App\Models\StockTransferItem::whereHas('stockTransfer', function ($q) use ($warehouseId) {
            $q->where('status', 'in_transit');
            if ($warehouseId) {
                $q->where('destination_warehouse_id', $warehouseId);
            }
        })
        ->whereHas('batch', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        })
        ->sum('quantity');

        return $fromPO + $fromTransfer;
    }

    /**
     * Check if overselling is allowed based on company policy
     */
    public function isOversellingAllowed(?int $companyId = null): bool
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        $company = Company::find($companyId);
        
        return ($company?->stock_limit_mode ?? 'block') === 'warning';
    }

    /**
     * Get stock summary with availability and incoming
     */
    public function getStockSummary(int $productId, ?int $warehouseId = null): array
    {
        $available = $this->getAvailableStock($productId, $warehouseId);
        $incoming = $this->getIncomingStock($productId, $warehouseId);

        return [
            'available' => $available,
            'incoming' => $incoming,
            'total_expected' => $available + $incoming,
            'overselling_allowed' => $this->isOversellingAllowed(),
        ];
    }
}
