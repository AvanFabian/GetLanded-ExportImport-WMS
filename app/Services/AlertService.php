<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlertService
{
    /**
     * Get all active alerts
     */
    public function getAlerts(): Collection
    {
        $alerts = collect();

        $alerts = $alerts->merge($this->getLowStockAlerts());
        $alerts = $alerts->merge($this->getPaymentDueAlerts());
        $alerts = $alerts->merge($this->getStaleStockAlerts());

        return $alerts;
    }

    /**
     * Check for Low Stock
     */
    public function getLowStockAlerts(): Collection
    {
        // Get products where total stock is <= min_stock
        $products = Product::query()
            ->where('status', true) // Active products only
            ->whereHas('warehouses') // Only if they are assigned to a warehouse
            ->get()
            ->filter(function ($product) {
                // Using the accessor getTotalStockAttribute logic we saw earlier
                return $product->total_stock <= $product->min_stock;
            });

        return $products->map(function ($product) {
            return [
                'type' => 'low_stock',
                'priority' => 'high',
                'message' => "Low Stock: {$product->name} ({$product->total_stock} / {$product->min_stock})",
                'action_url' => route('inventory.index', ['search' => $product->code]),
                'created_at' => now(),
            ];
        });
    }

    /**
     * Check for Payment Due (Invoices due within 24h)
     */
    public function getPaymentDueAlerts(): Collection
    {
        // Assuming SalesOrder has payment status 'unpaid' or 'partial' and due_date
        // Actually SalesOrder doesn't have due_date in the file I viewed (only delivery_date).
        // I'll check if Invoice model exists or if I should use delivery_date or created_at + term.
        // For now, I'll assume SalesOrder with 'delivery_date' is the proxy for urgency if unpaid.
        
        $orders = SalesOrder::query()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('delivery_date', '<=', Carbon::now()->addHours(24))
            ->where('status', '!=', 'cancelled')
            ->get();

        return $orders->map(function ($order) {
            return [
                'type' => 'payment_due',
                'priority' => 'medium',
                'message' => "Payment Pending: {$order->so_number} (Delivery: {$order->delivery_date->format('d M')})",
                'action_url' => route('sales_orders.show', $order->id),
                'created_at' => now(),
            ];
        });
    }

    /**
     * Check for Stale Stock (No movement in 90 days)
     */
    public function getStaleStockAlerts(): Collection
    {
        $ninetyDaysAgo = Carbon::now()->subDays(90);

        // Find batches created > 90 days ago that still have quantity > 0
        // And check if they haven't moved recently.
        // Simplified: Just batches created > 90 days ago with stock.
        
        $batches = Batch::query()
            ->where('created_at', '<', $ninetyDaysAgo)
            ->where('status', 'active')
            ->whereHas('stockLocations', function ($q) {
                $q->where('quantity', '>', 0);
            })
            ->limit(5) // Limit to avoid flooding
            ->get();

        return $batches->map(function ($batch) {
            return [
                'type' => 'stale_stock',
                'priority' => 'low',
                'message' => "Stale Stock: Batch {$batch->batch_number} (" . ($batch->product->name ?? 'Unknown') . ")",
                'action_url' => route('inventory.batches.index', ['search' => $batch->batch_number]),
                'created_at' => now(),
            ];
        });
    }
}
