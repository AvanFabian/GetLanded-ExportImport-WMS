<?php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
use App\Services\PickingListService;
use App\Services\BatchService;
use App\Models\SalesOrder;
use App\Models\Batch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperationsController extends Controller
{
    public function __construct(
        protected PickingListService $pickingService,
        protected BatchService $batchService
    ) {}

    public function pickingList(SalesOrder $order, Request $request)
    {
        $this->authorize('view', $order);

        $strategy = $request->get('strategy', 'FIFO');
        $warehouseId = $request->get('warehouse_id');

        $result = $this->pickingService->generate($order, $strategy, $warehouseId);

        return Inertia::render('Operations/PickingList', [
            'order' => $order->load('customer', 'items.product'),
            'pickingList' => $result['picking_list'],
            'isComplete' => $result['complete'],
            'strategy' => $strategy,
        ]);
    }

    public function confirmPicking(Request $request, SalesOrder $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'picks' => 'required|array',
            'picks.*.location_id' => 'required|exists:stock_locations,id',
            'picks.*.pick_quantity' => 'required|integer|min:1',
        ]);

        $picks = collect($validated['picks']);
        
        // Validate picks
        $errors = $this->pickingService->validatePicks($picks);
        if (!empty($errors)) {
            return back()->withErrors(['picks' => $errors]);
        }

        $this->pickingService->confirmPicks($validated['picks']);

        return back()->with('success', 'Picking confirmed. Stock has been reserved.');
    }

    public function splitBatch(Request $request, Batch $batch)
    {
        $this->authorize('update', $batch);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'grade' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $newBatch = $this->batchService->split($batch, $validated['quantity'], [
                'grade' => $validated['grade'] ?? $batch->grade,
                'notes' => $validated['notes'] ?? null,
            ]);

            return back()->with('success', "Batch split successfully. New batch: {$newBatch->batch_number}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function quarantineBatch(Request $request, Batch $batch)
    {
        $this->authorize('update', $batch);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->batchService->quarantine($batch, $validated['reason']);

        return back()->with('success', 'Batch placed in quarantine.');
    }

    public function releaseBatch(Batch $batch)
    {
        $this->authorize('update', $batch);

        $this->batchService->releaseFromQuarantine($batch);

        return back()->with('success', 'Batch released from quarantine.');
    }

    public function batchTraceability(Batch $batch)
    {
        $this->authorize('view', $batch);

        $tree = $this->batchService->getTraceabilityTree($batch);

        return Inertia::render('Batches/Traceability', [
            'batch' => $batch->load('product'),
            'tree' => $tree,
        ]);
    }

    public function securityLogs(Request $request)
    {
        // Admin only
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $logs = SecurityLog::where('company_id', auth()->user()->company_id)
            ->with('user')
            ->when($request->event, fn($q, $e) => $q->where('event', $e))
            ->when($request->user_id, fn($q, $id) => $q->where('user_id', $id))
            ->latest()
            ->paginate(50);

        return Inertia::render('Settings/SecurityLogs', [
            'logs' => $logs,
            'filters' => $request->only(['event', 'user_id']),
        ]);
    }
}
