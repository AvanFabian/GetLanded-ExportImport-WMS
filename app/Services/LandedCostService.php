<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Landed Cost Allocation Engine
 *
 * Takes a list of line items and shipment expenses, then allocates each expense
 * across items based on the expense's allocation method (value, quantity, weight, volume).
 *
 * Returns the allocated cost PER UNIT for each product_id.
 *
 * Usage:
 *   $service = new LandedCostService();
 *   $allocations = $service->allocate($items, $expenses);
 *   // $allocations = [product_id => allocated_cost_per_unit, ...]
 */
class LandedCostService
{
    /**
     * Allocate shipment expenses across line items.
     *
     * @param  array  $items    Each item: ['product_id', 'quantity', 'unit_price', 'total_line_value']
     * @param  Collection  $expenses  ShipmentExpense models
     * @return array  [product_id => allocated_cost_per_unit]
     */
    public function allocate(array $items, Collection $expenses): array
    {
        if (empty($items) || $expenses->isEmpty()) {
            return array_fill_keys(array_column($items, 'product_id'), 0);
        }

        // Preload product weight/volume data for weight/volume allocation
        $productIds = array_column($items, 'product_id');
        $products = Product::withoutGlobalScopes()
            ->whereIn('id', $productIds)
            ->get(['id', 'net_weight', 'cbm_volume'])
            ->keyBy('id');

        // Compute totals for each allocation method
        $totals = $this->computeTotals($items, $products);

        // Initialize allocation accumulator per product
        $allocations = array_fill_keys($productIds, 0);

        // Process each expense
        foreach ($expenses as $expense) {
            $expenseAmountIdr = $this->convertToIdr($expense->amount, $expense->currency_code);
            $method = $expense->allocation_method ?? 'value';

            foreach ($items as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $product = $products->get($pid);

                $ratio = $this->calculateRatio($method, $item, $product, $totals);

                // Allocated amount for this item's TOTAL quantity, then divided by qty = per unit
                if ($qty > 0) {
                    $allocations[$pid] += ($expenseAmountIdr * $ratio) / $qty;
                }
            }
        }

        // Round to 2 decimal places
        return array_map(fn($v) => round($v, 2), $allocations);
    }

    /**
     * Compute aggregate totals needed for ratio calculations.
     */
    private function computeTotals(array $items, Collection $products): array
    {
        $totalValue = 0;
        $totalQuantity = 0;
        $totalWeight = 0;
        $totalVolume = 0;

        foreach ($items as $item) {
            $pid = $item['product_id'];
            $product = $products->get($pid);

            $totalValue += $item['total_line_value'] ?? ($item['quantity'] * $item['unit_price']);
            $totalQuantity += $item['quantity'];
            $totalWeight += ($product?->net_weight ?? 0) * $item['quantity'];
            $totalVolume += ($product?->cbm_volume ?? 0) * $item['quantity'];
        }

        return [
            'value' => $totalValue,
            'quantity' => $totalQuantity,
            'weight' => $totalWeight,
            'volume' => $totalVolume,
        ];
    }

    /**
     * Calculate an item's share ratio for a given allocation method.
     *
     * If the selected method can't be used (e.g., weight allocation but no
     * products have weight data), falls back to quantity allocation.
     */
    private function calculateRatio(string $method, array $item, ?Product $product, array $totals): float
    {
        switch ($method) {
            case 'value':
                if ($totals['value'] <= 0) return 0;
                $itemValue = $item['total_line_value'] ?? ($item['quantity'] * $item['unit_price']);
                return $itemValue / $totals['value'];

            case 'weight':
                // Fallback to quantity if no weight data
                if ($totals['weight'] <= 0) {
                    return $this->calculateRatio('quantity', $item, $product, $totals);
                }
                $itemWeight = ($product?->net_weight ?? 0) * $item['quantity'];
                return $itemWeight / $totals['weight'];

            case 'volume':
                // Fallback to quantity if no volume data
                if ($totals['volume'] <= 0) {
                    return $this->calculateRatio('quantity', $item, $product, $totals);
                }
                $itemVolume = ($product?->cbm_volume ?? 0) * $item['quantity'];
                return $itemVolume / $totals['volume'];

            case 'quantity':
            default:
                if ($totals['quantity'] <= 0) return 0;
                return $item['quantity'] / $totals['quantity'];
        }
    }

    /**
     * Convert an expense amount to IDR using the Currency model's exchange rate.
     *
     * If the expense is already in IDR (or the base currency), return as-is.
     * If no rate is found, assume 1:1 (safe fallback — user should set rates).
     */
    private function convertToIdr(float $amount, ?string $currencyCode): float
    {
        if (empty($currencyCode) || strtoupper($currencyCode) === 'IDR') {
            return $amount;
        }

        $currency = Currency::findByCode($currencyCode);
        if (!$currency || $currency->is_base) {
            return $amount;
        }

        // exchange_rate = how many IDR per 1 unit of this currency
        // e.g., USD exchange_rate = 15500 means 1 USD = 15,500 IDR
        return $amount * (float) $currency->exchange_rate;
    }

    /**
     * Update Weighted Average Cost for a product after receiving new stock.
     *
     * WAC = ((existing_qty × existing_cost) + (incoming_qty × incoming_cost)) / total_qty
     *
     * @param  int    $productId      Product to update
     * @param  int    $incomingQty    Quantity being received
     * @param  float  $incomingCost   Unit cost INCLUDING allocated landed cost
     */
    public function updateWAC(int $productId, int $incomingQty, float $incomingCost): void
    {
        $product = Product::withoutGlobalScopes()->find($productId);
        if (!$product) return;

        $currentStock = $product->total_stock;
        $currentCost = $product->cost; // Uses WAC or falls back to purchase_price

        $totalQty = $currentStock + $incomingQty;

        if ($totalQty > 0) {
            $newWAC = (($currentStock * $currentCost) + ($incomingQty * $incomingCost)) / $totalQty;
            $product->update(['weighted_average_cost' => round($newWAC, 2)]);
        }
    }
}
