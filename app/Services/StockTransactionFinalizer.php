<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Exceptions\SelfApprovalException;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\BatchMovement;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\StockLocation;
use App\Models\StockOpname;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\User;
use App\Notifications\TransactionApprovedNotification;
use App\Notifications\TransactionRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * StockTransactionFinalizer
 * 
 * The ONLY service that executes stock manipulation logic.
 * Called exclusively by approve() methods for maker-checker compliance.
 * 
 * Key features:
 * - Self-approval prevention
 * - Atomic transactions with lockForUpdate()
 * - Audit trail creation
 */
class StockTransactionFinalizer
{
    /**
     * Approve and execute a Stock-In transaction.
     *
     * @param StockIn $stockIn
     * @param User $approver
     * @return StockIn
     * @throws SelfApprovalException
     */
    public function approveStockIn(StockIn $stockIn, User $approver): StockIn
    {
        // Self-approval prevention
        $this->preventSelfApproval($stockIn->created_by, $approver->id);

        // Verify status
        if ($stockIn->status !== TransactionStatus::PENDING_APPROVAL->value) {
            throw new \RuntimeException('Stock-In is not pending approval');
        }

        DB::beginTransaction();
        try {
            // Update transaction status
            $stockIn->update([
                'status' => TransactionStatus::COMPLETED->value,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Execute stock manipulation for each detail
            foreach ($stockIn->details as $detail) {
                $this->executeStockInDetail($detail, $approver);
            }

            DB::commit();

            // Send notification to creator
            if ($stockIn->creator) {
                $stockIn->creator->notify(new TransactionApprovedNotification($stockIn, $approver));
            }

            Log::info('Stock-In approved', [
                'stock_in_id' => $stockIn->id,
                'approved_by' => $approver->id,
            ]);

            return $stockIn->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock-In approval failed', [
                'stock_in_id' => $stockIn->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute stock manipulation for a single stock-in detail.
     */
    protected function executeStockInDetail(StockInDetail $detail, User $approver): void
    {
        $stockLocation = StockLocation::where('batch_id', $detail->batch_id)
            ->where('bin_id', $detail->bin_id)
            ->lockForUpdate()
            ->first();

        $previousQty = $stockLocation ? $stockLocation->quantity : 0;

        if ($stockLocation) {
            $stockLocation->increment('quantity', $detail->quantity);
        } else {
            $stockLocation = StockLocation::create([
                'batch_id' => $detail->batch_id,
                'bin_id' => $detail->bin_id,
                'quantity' => $detail->quantity,
                'reserved_quantity' => 0,
            ]);
        }

        // Create batch movement record
        BatchMovement::create([
            'batch_id' => $detail->batch_id,
            'destination_bin_id' => $detail->bin_id,
            'quantity' => $detail->quantity,
            'movement_type' => 'stock_in',
            'reference_type' => StockIn::class,
            'reference_id' => $detail->stock_in_id,
            'performed_by' => $approver->id,
            'notes' => "Stock-In approved by {$approver->name}",
        ]);
    }

    /**
     * Approve and execute a Stock-Out transaction.
     *
     * @param StockOut $stockOut
     * @param User $approver
     * @return StockOut
     * @throws SelfApprovalException
     * @throws InsufficientStockException
     */
    public function approveStockOut(StockOut $stockOut, User $approver): StockOut
    {
        // Self-approval prevention
        $this->preventSelfApproval($stockOut->created_by, $approver->id);

        // Verify status
        if ($stockOut->status !== TransactionStatus::PENDING_APPROVAL->value) {
            throw new \RuntimeException('Stock-Out is not pending approval');
        }

        DB::beginTransaction();
        try {
            // Update transaction status
            $stockOut->update([
                'status' => TransactionStatus::COMPLETED->value,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Execute stock manipulation for each detail
            foreach ($stockOut->details as $detail) {
                $this->executeStockOutDetail($detail, $approver);
            }

            DB::commit();

            // Send notification to creator
            if ($stockOut->creator) {
                $stockOut->creator->notify(new TransactionApprovedNotification($stockOut, $approver));
            }

            Log::info('Stock-Out approved', [
                'stock_out_id' => $stockOut->id,
                'approved_by' => $approver->id,
            ]);

            return $stockOut->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock-Out approval failed', [
                'stock_out_id' => $stockOut->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute stock manipulation for a single stock-out detail.
     */
    protected function executeStockOutDetail(StockOutDetail $detail, User $approver): void
    {
        $stockLocation = StockLocation::where('batch_id', $detail->batch_id)
            ->where('bin_id', $detail->bin_id)
            ->lockForUpdate()
            ->firstOrFail();

        // Verify sufficient stock
        if ($stockLocation->quantity < $detail->quantity) {
            throw new InsufficientStockException(
                "Insufficient stock in batch {$detail->batch_id}"
            );
        }

        $stockLocation->decrement('quantity', $detail->quantity);

        // Create batch movement record
        BatchMovement::create([
            'batch_id' => $detail->batch_id,
            'source_bin_id' => $detail->bin_id,
            'quantity' => $detail->quantity,
            'movement_type' => 'stock_out',
            'reference_type' => StockOut::class,
            'reference_id' => $detail->stock_out_id,
            'performed_by' => $approver->id,
            'notes' => "Stock-Out approved by {$approver->name}",
        ]);
    }

    /**
     * Approve and execute a Stock Opname (adjustment).
     *
     * @param StockOpname $opname
     * @param User $approver
     * @return StockOpname
     * @throws SelfApprovalException
     */
    public function approveStockOpname(StockOpname $opname, User $approver): StockOpname
    {
        // Self-approval prevention
        $this->preventSelfApproval($opname->user_id, $approver->id);

        // Verify status
        if ($opname->status !== TransactionStatus::PENDING_APPROVAL->value) {
            throw new \RuntimeException('Stock Opname is not pending approval');
        }

        DB::beginTransaction();
        try {
            // Update opname status
            $opname->update([
                'status' => TransactionStatus::COMPLETED->value,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Execute the adjustment
            $stockLocation = StockLocation::where('batch_id', $opname->batch_id)
                ->where('bin_id', $opname->bin_id)
                ->lockForUpdate()
                ->first();

            if ($stockLocation) {
                $stockLocation->update(['quantity' => $opname->counted_qty]);
            } else {
                StockLocation::create([
                    'batch_id' => $opname->batch_id,
                    'bin_id' => $opname->bin_id,
                    'quantity' => $opname->counted_qty,
                    'reserved_quantity' => 0,
                ]);
            }

            // Create batch movement for the adjustment
            if ($opname->difference != 0) {
                BatchMovement::create([
                    'batch_id' => $opname->batch_id,
                    $opname->difference > 0 ? 'destination_bin_id' : 'source_bin_id' => $opname->bin_id,
                    'quantity' => abs($opname->difference),
                    'movement_type' => 'adjustment',
                    'reference_type' => StockOpname::class,
                    'reference_id' => $opname->id,
                    'performed_by' => $approver->id,
                    'notes' => "Stock Opname approved: {$opname->reason}",
                ]);
            }

            DB::commit();

            Log::info('Stock Opname approved', [
                'opname_id' => $opname->id,
                'approved_by' => $approver->id,
                'adjustment' => $opname->difference,
            ]);

            return $opname->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock Opname approval failed', [
                'opname_id' => $opname->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject a transaction.
     *
     * @param StockIn|StockOut|StockOpname $transaction
     * @param User $rejector
     * @param string $reason
     * @return mixed
     */
    public function reject($transaction, User $rejector, string $reason)
    {
        $transaction->update([
            'status' => TransactionStatus::REJECTED->value,
            'rejected_by' => $rejector->id,
            'rejection_notes' => $reason,
        ]);

        // Notify creator
        if (method_exists($transaction, 'creator') && $transaction->creator) {
            $transaction->creator->notify(new TransactionRejectedNotification($transaction, $rejector, $reason));
        }

        Log::info('Transaction rejected', [
            'type' => class_basename($transaction),
            'id' => $transaction->id,
            'rejected_by' => $rejector->id,
            'reason' => $reason,
        ]);

        return $transaction->fresh();
    }

    /**
     * Prevent self-approval.
     *
     * @param int|null $creatorId
     * @param int $approverId
     * @throws SelfApprovalException
     */
    protected function preventSelfApproval(?int $creatorId, int $approverId): void
    {
        if ($creatorId === $approverId) {
            throw new SelfApprovalException('You cannot approve your own transaction');
        }
    }
}
