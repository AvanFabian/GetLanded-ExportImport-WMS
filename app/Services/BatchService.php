<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\StockLocation;
use Illuminate\Support\Facades\DB;

class BatchService
{
    /**
     * Split a batch into two batches
     * 
     * @param Batch $batch Original batch
     * @param float $splitQuantity Quantity for the new batch
     * @param array $newAttributes Optional attributes for the new batch
     * @return Batch The new split batch
     */
    public function split(Batch $batch, float $splitQuantity, array $newAttributes = []): Batch
    {
        return DB::transaction(function () use ($batch, $splitQuantity, $newAttributes) {
            // Find location with this batch
            $location = StockLocation::where('batch_id', $batch->id)
                ->orderByDesc('quantity')
                ->first();

            if (!$location || $location->quantity < $splitQuantity) {
                throw new \Exception("Insufficient quantity to split. Available: " . ($location?->quantity ?? 0));
            }

            // Create new batch
            $newBatch = $batch->replicate();
            $newBatch->batch_number = $this->generateSplitBatchNumber($batch);
            $newBatch->parent_batch_id = $batch->id;
            $newBatch->fill($newAttributes);
            $newBatch->save();

            // Adjust stock locations
            $location->decrement('quantity', $splitQuantity);

            // Create new location for split batch
            StockLocation::create([
                'batch_id' => $newBatch->id,
                'bin_id' => $location->bin_id,
                'quantity' => $splitQuantity,
                'reserved_quantity' => 0,
            ]);

            return $newBatch;
        });
    }

    /**
     * Merge two batches into one
     */
    public function merge(Batch $primary, Batch $secondary): Batch
    {
        return DB::transaction(function () use ($primary, $secondary) {
            if ($primary->product_id !== $secondary->product_id) {
                throw new \Exception("Cannot merge batches of different products");
            }

            // Move all stock from secondary to primary
            $secondaryLocations = StockLocation::where('batch_id', $secondary->id)->get();
            
            foreach ($secondaryLocations as $secLoc) {
                $primaryLoc = StockLocation::firstOrCreate(
                    ['batch_id' => $primary->id, 'bin_id' => $secLoc->bin_id],
                    ['quantity' => 0, 'reserved_quantity' => 0]
                );
                
                $primaryLoc->increment('quantity', $secLoc->quantity);
                $secLoc->delete();
            }

            // Mark secondary as depleted
            $secondary->update(['status' => 'depleted']);

            return $primary->fresh();
        });
    }

    /**
     * Quarantine a batch
     */
    public function quarantine(Batch $batch, string $reason): Batch
    {
        $batch->update([
            'is_quarantined' => true,
            'quarantine_reason' => $reason,
        ]);

        return $batch;
    }

    /**
     * Release batch from quarantine
     */
    public function releaseFromQuarantine(Batch $batch): Batch
    {
        $batch->update([
            'is_quarantined' => false,
            'quarantine_reason' => null,
        ]);

        return $batch;
    }

    /**
     * Check if quantity change qualifies as minor shrinkage (< 5%)
     */
    public function isMinorShrinkage(Batch $batch, float $quantity): bool
    {
        $totalQuantity = StockLocation::where('batch_id', $batch->id)->sum('quantity');
        
        if ($totalQuantity == 0) return false;
        
        $shrinkagePercent = ($quantity / $totalQuantity) * 100;
        
        return $shrinkagePercent < 5;
    }

    /**
     * Get batch traceability tree
     */
    public function getTraceabilityTree(Batch $batch): array
    {
        $tree = [
            'batch' => $batch,
            'parent' => null,
            'children' => [],
        ];

        // Get parent chain
        if ($batch->parent_batch_id) {
            $parent = Batch::find($batch->parent_batch_id);
            if ($parent) {
                $tree['parent'] = $this->getTraceabilityTree($parent);
            }
        }

        // Get children
        $children = Batch::where('parent_batch_id', $batch->id)->get();
        foreach ($children as $child) {
            $tree['children'][] = $this->getTraceabilityTree($child);
        }

        return $tree;
    }

    protected function generateSplitBatchNumber(Batch $original): string
    {
        $splitCount = Batch::where('parent_batch_id', $original->id)->count();
        return $original->batch_number . '-S' . ($splitCount + 1);
    }
}
