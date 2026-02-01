<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    public function index(Request $request) {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        // Get Completed Sales Orders within Date Range
        $salesOrders = \App\Models\SalesOrder::with('items')
            ->where('status', 'completed') // or 'delivered' depending on business definition
            ->whereBetween('order_date', [$startDate, $endDate])
            ->get();

        $revenue = 0;
        $cogs = 0;
        $grossProfit = 0;

        $chartLabels = [];
        $chartProfit = [];

        // Daily Aggregation for Chart
        $dailyData = $salesOrders->groupBy(function($order) {
            return $order->order_date->format('Y-m-d');
        });

        foreach ($dailyData as $date => $orders) {
            $dailyRevenue = 0;
            $dailyCOGS = 0;

            foreach ($orders as $order) {
                // Revenue = Net Amount (excluding Tax/Shipping usually, using Subtotal here for simplicity)
                $dailyRevenue += $order->subtotal; // Use subtotal (price * qty)

                // COGS = Sum of (Item Qty * Cost Basis)
                foreach ($order->items as $item) {
                     $cost = $item->cost_basis ?? 0; // Fallback to 0 if legacy
                     $dailyCOGS += ($item->quantity * $cost);
                }
            }

            $chartLabels[] = $date;
            $chartProfit[] = $dailyRevenue - $dailyCOGS;
            
            $revenue += $dailyRevenue;
            $cogs += $dailyCOGS;
        }

        $grossProfit = $revenue - $cogs;
        $marginPercentage = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        return view('reports.profit_loss', compact(
            'revenue', 'cogs', 'grossProfit', 'marginPercentage', 
            'startDate', 'endDate', 'chartLabels', 'chartProfit'
        ));
    }
}
