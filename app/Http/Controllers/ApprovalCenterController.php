<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockOut;
use App\Enums\TransactionStatus;
use Illuminate\Http\Request;

/**
 * ApprovalCenterController
 * 
 * Centralized view for all pending approvals.
 * Aggregates Stock In, Stock Out, and other pending transactions.
 */
class ApprovalCenterController extends Controller
{
    /**
     * Display the approval center dashboard.
     */
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('transaction.approve')) {
            abort(403, 'You do not have permission to approve transactions.');
        }

        // Get pending transactions
        $pendingStockIns = StockIn::where('status', TransactionStatus::PENDING_APPROVAL->value)
            ->with(['warehouse', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $pendingStockOuts = StockOut::where('status', TransactionStatus::PENDING_APPROVAL->value)
            ->with(['warehouse', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Count badges
        $counts = [
            'stock_ins' => $pendingStockIns->count(),
            'stock_outs' => $pendingStockOuts->count(),
            'total' => $pendingStockIns->count() + $pendingStockOuts->count(),
        ];

        return view('approvals.index', compact('pendingStockIns', 'pendingStockOuts', 'counts'));
    }

    /**
     * Quick approve a transaction.
     */
    public function approve(Request $request, string $type, int $id)
    {
        if (!auth()->user()->hasPermissionTo('transaction.approve')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $model = $this->getModel($type, $id);
        
        if (!$model) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Check self-approval
        if ($model->created_by === auth()->id()) {
            return response()->json(['error' => 'Cannot approve your own transaction'], 422);
        }

        $model->update([
            'status' => TransactionStatus::COMPLETED->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Transaction approved']);
    }

    /**
     * Quick reject a transaction.
     */
    public function reject(Request $request, string $type, int $id)
    {
        if (!auth()->user()->hasPermissionTo('transaction.reject')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $model = $this->getModel($type, $id);
        
        if (!$model) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $model->update([
            'status' => TransactionStatus::REJECTED->value,
            'rejection_reason' => $validated['reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Transaction rejected']);
    }

    /**
     * Get pending counts for sidebar badge.
     */
    public function counts()
    {
        if (!auth()->user()->hasPermissionTo('transaction.approve')) {
            return response()->json(['total' => 0]);
        }

        $total = StockIn::where('status', TransactionStatus::PENDING_APPROVAL->value)->count()
               + StockOut::where('status', TransactionStatus::PENDING_APPROVAL->value)->count();

        return response()->json(['total' => $total]);
    }

    /**
     * Get model by type and ID.
     */
    protected function getModel(string $type, int $id)
    {
        return match($type) {
            'stock-in' => StockIn::find($id),
            'stock-out' => StockOut::find($id),
            default => null,
        };
    }
}
