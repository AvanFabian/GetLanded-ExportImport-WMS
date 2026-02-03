<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Currency;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\InboundShipment;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\StockOut;
use App\Models\StockLocation;
use App\Models\WarehouseBin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Executive Dashboard with data visualization.
     */
    public function index(\App\Services\TrackingService $trackingService)
    {
        // 1. Live Shipment Tracker (Using Predictive Service)
        $mapData = $trackingService->getDashboardMapData(auth()->user()->company_id);

        /**
         * 2. Key Performance Indicators (KPIs)
         */
        
        // Total Stock Value
        $stockValue = $this->calculateStockValue();

        // Warehouse Fill Rate
        $fillRate = $this->getWarehouseFillRate();

        // 7-Day Sales Trend (Placeholder for now)
        $salesTrend = [12, 19, 3, 5, 2, 3, 15]; // Dummy data

        /**
         * 3. Actionable Widgets
         */
        $alerts = $this->getActiveAlerts(); 
        
        $expiringSoon = Batch::where('company_id', auth()->user()->company_id)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('status', '!=', 'depleted')
            ->whereHas('stockLocations', fn($q) => $q->where('quantity', '>', 0))
            ->with('product')
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        $lowStock = Product::where('company_id', auth()->user()->company_id)
            ->whereColumn('stock', '<=', 'min_stock')
            ->take(5)
            ->get();
            
        $activeAlerts = [
            'total' => $expiringSoon->count() + $lowStock->count(),
            'low_stock' => $lowStock->count(),
            'expiring' => $expiringSoon->count()
        ];

        /**
         * 4. Charts Data
         */
        $stockTrends = $this->getStockTrends();
        $zoneDistribution = $this->getZoneDistribution();
        
        // 5. Financials
        $monthlyProfit = $this->getMonthlyProfitData();
        $monthlyFees = $this->getMonthlyFees();
        $usdRate = Currency::where('code', 'USD')->value('exchange_rate') ?? 16000;
        
        // 6. Recent Activity
        $recentActivity = AuditLog::where('company_id', auth()->user()->company_id)
            ->with('user')
            ->latest()
            ->take(5)
            ->get();
            
        // 7. Supply Chain & Shipments
        $onWaterValue = $this->getOnWaterValue();
        $inventoryAging = $this->getInventoryAgingData();
        $topProducts = $this->getTopProfitableProducts();
        $holidayWarnings = $this->getHolidayWarnings();
        $incomingShipments = InboundShipment::where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['on_water', 'booked'])
            ->with('purchaseOrders.supplier')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'mapData',
            'stockValue',
            'fillRate',
            'salesTrend',
            'activeAlerts',
            'stockTrends',
            'zoneDistribution',
            'monthlyProfit',
            'monthlyFees',
            'usdRate',
            'recentActivity',
            'onWaterValue',
            'inventoryAging',
            'topProducts',
            'holidayWarnings',
            'incomingShipments',
            'expiringSoon'
        ));
    }

    
    // Helper to get Holiday Warnings (Cached in Service)
    private function getHolidayWarnings()
    {
         return (new \App\Services\HolidayService())->getSupplyChainWarnings();
    }
    private function getOnWaterValue()
    {
         // Sum of POs inside Active Shipments
         return \App\Models\PurchaseOrderDetail::whereHas('purchaseOrder', function($q){
             $q->whereHas('inboundShipment', function($sq){
                 $sq->where('status', '!=', 'received')->where('status', '!=', 'draft');
             });
         })->selectRaw('SUM(quantity_ordered * unit_price) as total')->value('total') ?? 0;
    }
    private function getMonthlyProfitData(): array
    {
        $labels = [];
        $revenue = [];
        $netProfit = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $monthData = SalesOrder::whereYear('order_date', $date->year)
                ->whereMonth('order_date', $date->month)
                ->selectRaw('COALESCE(SUM(total), 0) as revenue, COALESCE(SUM(net_amount), 0) as net')
                ->first();

            $revenue[] = (float) ($monthData->revenue ?? 0);
            $netProfit[] = (float) ($monthData->net ?? 0);
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'netProfit' => $netProfit,
        ];
    }

    /**
     * Get inventory aging breakdown.
     */
    private function getInventoryAgingData(): array
    {
        $now = now();
        
        $fresh = Batch::where('status', 'available')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->whereHas('stockLocations', fn($q) => $q->where('quantity', '>', 0))
            ->count();

        $medium = Batch::where('status', 'available')
            ->whereBetween('created_at', [$now->copy()->subDays(90), $now->copy()->subDays(30)])
            ->whereHas('stockLocations', fn($q) => $q->where('quantity', '>', 0))
            ->count();

        $slow = Batch::where('status', 'available')
            ->where('created_at', '<', $now->copy()->subDays(90))
            ->whereHas('stockLocations', fn($q) => $q->where('quantity', '>', 0))
            ->count();

        return [
            'labels' => ['Fresh (<30 days)', 'Medium (30-90 days)', 'Slow Moving (>90 days)'],
            'data' => [$fresh, $medium, $slow],
            'colors' => ['#10B981', '#F59E0B', '#EF4444'],
        ];
    }

    /**
     * Get top 5 most profitable products.
     */
    private function getTopProfitableProducts(): array
    {
        $products = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.order_date', '>=', now()->subMonths(6))
            ->selectRaw('products.name, products.code, SUM(sales_order_items.subtotal) as total_sales, COUNT(*) as order_count')
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();

        return [
            'labels' => $products->pluck('code')->toArray(),
            'data' => $products->pluck('total_sales')->map(fn($v) => (float) $v)->toArray(),
            'names' => $products->pluck('name')->toArray(),
        ];
    }

    /**
     * Calculate total stock value in IDR and USD.
     */
    private function calculateStockValue(): array
    {
        $totalValueIdr = StockLocation::join('batches', 'stock_locations.batch_id', '=', 'batches.id')
            ->selectRaw('SUM(stock_locations.quantity * COALESCE(batches.cost_price, 0)) as total')
            ->value('total') ?? 0;

        $usdRate = Currency::where('code', 'USD')->first()?->exchange_rate ?? 15850;
        $totalValueUsd = $usdRate > 0 ? $totalValueIdr / $usdRate : 0;

        return [
            'idr' => $totalValueIdr,
            'usd' => $totalValueUsd,
        ];
    }

    /**
     * Get count of active alerts (low stock + expiring).
     */
    private function getActiveAlerts(): array
    {
        // Low stock count (products below min_stock in any warehouse)
        $lowStockCount = Product::whereHas('warehouses', function ($query) {
            $query->whereRaw('product_warehouse.stock < products.min_stock');
        })->count();

        // Expiring within 30 days
        $expiringCount = Batch::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('status', '!=', 'depleted')
            ->whereHas('stockLocations', fn($q) => $q->where('quantity', '>', 0))
            ->count();

        return [
            'low_stock' => $lowStockCount,
            'expiring' => $expiringCount,
            'total' => $lowStockCount + $expiringCount,
        ];
    }

    /**
     * Get total transaction fees this month.
     */
    private function getMonthlyFees(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $salesFees = SalesOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('transaction_fees') ?? 0;

        $purchaseFees = DB::table('purchase_orders')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('transaction_fees') ?? 0;

        return [
            'sales' => $salesFees,
            'purchases' => $purchaseFees,
            'total' => $salesFees + $purchaseFees,
        ];
    }

    /**
     * Calculate warehouse fill rate (occupied bins / total bins).
     */
    private function getWarehouseFillRate(): array
    {
        $totalBins = WarehouseBin::where('is_active', true)->count();
        
        // Bins with stock
        $occupiedBins = WarehouseBin::where('is_active', true)
            ->whereHas('stockLocations', fn($q) => $q->where('quantity', '>', 0))
            ->count();

        $fillPercentage = $totalBins > 0 ? round(($occupiedBins / $totalBins) * 100, 1) : 0;

        return [
            'total_bins' => $totalBins,
            'occupied_bins' => $occupiedBins,
            'percentage' => $fillPercentage,
        ];
    }

    /**
     * Get stock trends for last 14 days.
     */
    private function getStockTrends(): array
    {
        $labels = [];
        $stockInData = [];
        $stockOutData = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');

            $stockInQty = StockInDetail::whereHas('stockIn', function ($q) use ($date) {
                $q->whereDate('date', $date);
            })->sum('quantity');

            $stockOutQty = DB::table('stock_out_details')
                ->join('stock_outs', 'stock_out_details.stock_out_id', '=', 'stock_outs.id')
                ->whereDate('stock_outs.date', $date)
                ->sum('stock_out_details.quantity') ?? 0;

            $stockInData[] = (int) $stockInQty;
            $stockOutData[] = (int) $stockOutQty;
        }

        return [
            'labels' => $labels,
            'stockIn' => $stockInData,
            'stockOut' => $stockOutData,
        ];
    }

    /**
     * Get stock distribution by zone.
     */
    private function getZoneDistribution(): array
    {
        $distribution = StockLocation::join('warehouse_bins', 'stock_locations.bin_id', '=', 'warehouse_bins.id')
            ->join('warehouse_racks', 'warehouse_bins.rack_id', '=', 'warehouse_racks.id')
            ->join('warehouse_zones', 'warehouse_racks.zone_id', '=', 'warehouse_zones.id')
            ->selectRaw('warehouse_zones.name as zone_name, SUM(stock_locations.quantity) as total_qty')
            ->where('stock_locations.quantity', '>', 0)
            ->groupBy('warehouse_zones.id', 'warehouse_zones.name')
            ->orderByDesc('total_qty')
            ->take(6)
            ->get();

        return [
            'labels' => $distribution->pluck('zone_name')->toArray(),
            'data' => $distribution->pluck('total_qty')->map(fn($v) => (int) $v)->toArray(),
        ];
    }
}
