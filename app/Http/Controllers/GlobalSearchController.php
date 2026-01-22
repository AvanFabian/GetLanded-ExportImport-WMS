<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use App\Services\InventoryReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GlobalSearchController extends Controller
{
    public function __construct(
        protected GlobalSearchService $searchService,
        protected InventoryReportService $reportService
    ) {}

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $results = $this->searchService->search(
            auth()->user()->company_id,
            $validated['q']
        );

        return response()->json(['results' => $results]);
    }

    public function deepSearch(Request $request)
    {
        $validated = $request->validate([
            'tracking' => 'required|string|min:3|max:100',
        ]);

        $results = $this->searchService->deepSearch(
            auth()->user()->company_id,
            $validated['tracking']
        );

        return response()->json(['results' => $results]);
    }

    public function agingReport()
    {
        $report = $this->reportService->agingReport(auth()->user()->company_id);

        return Inertia::render('Reports/InventoryAging', [
            'report' => $report->values(),
        ]);
    }

    public function cbmReport()
    {
        $report = $this->reportService->cbmReport(auth()->user()->company_id);

        return Inertia::render('Reports/CBMUtilization', [
            'report' => $report,
        ]);
    }

    public function reservationReport()
    {
        $report = $this->reportService->reservationSummary(auth()->user()->company_id);

        return Inertia::render('Reports/StockReservation', [
            'report' => $report->values(),
        ]);
    }

    public function businessHealth()
    {
        $report = $this->reportService->businessHealthReport(auth()->user()->company_id);

        return Inertia::render('Reports/BusinessHealth', [
            'report' => $report,
        ]);
    }
}
