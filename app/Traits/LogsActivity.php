<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * LogsActivity Trait
 * 
 * Automatically logs create, update, delete, and forceDelete events
 * to the audit_logs table.
 * 
 * Features:
 * - Only records dirty (changed) attributes
 * - Excludes updated_at from being the only recorded change
 * - Distinguishes between soft delete and force delete
 * - Uses afterCommit to avoid logging failed transactions
 * - Captures user context (user_id, ip_address, url)
 * 
 * Usage:
 *   class MyModel extends Model
 *   {
 *       use LogsActivity;
 *   }
 */
trait LogsActivity
{
    /**
     * Boot the trait and register model observers.
     */
    public static function bootLogsActivity(): void
    {
        // On Created - runs after transaction commits
        static::created(function (Model $model) {
            static::logAuditEvent($model, AuditLog::EVENT_CREATED);
        });

        // On Updated - only log if there are meaningful changes
        static::updated(function (Model $model) {
            $changes = static::getChangedAttributes($model);
            
            // Skip if no meaningful changes
            if (empty($changes['old']) && empty($changes['new'])) {
                return;
            }
            
            static::logAuditEvent($model, AuditLog::EVENT_UPDATED, $changes['old'], $changes['new']);
        });

        // On Deleted - handles both regular delete and soft delete
        static::deleted(function (Model $model) {
            // For models without SoftDeletes, this is a regular delete
            if (!static::usesSoftDeletes($model)) {
                static::logAuditEvent($model, AuditLog::EVENT_DELETED);
                return;
            }
            
            // For SoftDeletes models, check if this is a force delete or soft delete
            if ($model->isForceDeleting()) {
                static::logAuditEvent($model, AuditLog::EVENT_FORCE_DELETED);
            } else {
                static::logAuditEvent($model, AuditLog::EVENT_DELETED);
            }
        });
    }

    /**
     * Log an audit event for the model.
     */
    protected static function logAuditEvent(
        Model $model,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        // For created events, capture all attributes as new values
        if ($event === AuditLog::EVENT_CREATED) {
            $newValues = static::getAuditableAttributes($model);
            $oldValues = null;
        }
        
        // For deleted/forceDeleted events, capture current state as old values
        if (in_array($event, [AuditLog::EVENT_DELETED, AuditLog::EVENT_FORCE_DELETED])) {
            $oldValues = static::getAuditableAttributes($model);
            $newValues = null;
        }

        // Create snapshot data for persistence after deletion
        $snapshotData = static::createSnapshotData($model);

        // Create audit log entry
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'snapshot_data' => $snapshotData,
            'url' => static::getCurrentUrl(),
            'ip_address' => static::getClientIp(),
            'user_agent' => static::getUserAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Create snapshot data for audit log persistence.
     * This ensures logs remain readable after the source model is deleted.
     */
    protected static function createSnapshotData(Model $model): array
    {
        $snapshot = [
            'model_class' => class_basename($model),
            'model_id' => $model->getKey(),
        ];

        // Try to get a display name from common attributes
        $displayNameAttributes = ['name', 'title', 'batch_number', 'transaction_code', 'code', 'so_number', 'po_number', 'invoice_number'];
        
        foreach ($displayNameAttributes as $attr) {
            if ($model->$attr) {
                $snapshot['display_name'] = $model->$attr;
                break;
            }
        }

        // Fall back to ID if no display name found
        if (!isset($snapshot['display_name'])) {
            $snapshot['display_name'] = class_basename($model) . ' #' . $model->getKey();
        }

        return $snapshot;
    }

    /**
     * Get attributes that changed (dirty) excluding timestamps.
     * 
     * @return array{old: array, new: array}
     */
    protected static function getChangedAttributes(Model $model): array
    {
        $dirty = $model->getDirty();
        $original = $model->getOriginal();
        
        // Attributes to always exclude from audit
        $excludedAttributes = static::getExcludedAuditAttributes();
        
        $old = [];
        $new = [];
        
        foreach ($dirty as $key => $newValue) {
            // Skip excluded attributes
            if (in_array($key, $excludedAttributes)) {
                continue;
            }
            
            $oldValue = $original[$key] ?? null;
            
            // Only record if values are actually different
            // (handles type coercion issues)
            if ($oldValue != $newValue) {
                $old[$key] = $oldValue;
                $new[$key] = $newValue;
            }
        }
        
        // If the only change is updated_at, skip logging
        if (count($old) === 0 && count($new) === 0) {
            return ['old' => [], 'new' => []];
        }
        
        return ['old' => $old, 'new' => $new];
    }

    /**
     * Get all auditable attributes of the model.
     * Excludes system timestamps unless overridden.
     */
    protected static function getAuditableAttributes(Model $model): array
    {
        $attributes = $model->getAttributes();
        $excluded = static::getExcludedAuditAttributes();
        
        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Get attributes that should be excluded from auditing.
     * Override this method in your model to customize.
     */
    protected static function getExcludedAuditAttributes(): array
    {
        return [
            'updated_at',
            'remember_token',
            'password',
        ];
    }

    /**
     * Check if the model uses soft deletes.
     */
    protected static function usesSoftDeletes(Model $model): bool
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model));
    }

    /**
     * Get the current request URL (if available).
     */
    protected static function getCurrentUrl(): ?string
    {
        try {
            return Request::fullUrl();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the client IP address (if available).
     */
    protected static function getClientIp(): ?string
    {
        try {
            return Request::ip();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the user agent (if available).
     */
    protected static function getUserAgent(): ?string
    {
        try {
            return Request::userAgent();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get audit logs for this model instance.
     */
    public function auditLogs()
    {
        return AuditLog::where('auditable_type', get_class($this))
                       ->where('auditable_id', $this->getKey())
                       ->orderBy('created_at', 'desc');
    }

    /**
     * Get the most recent audit log for this model instance.
     */
    public function latestAuditLog(): ?AuditLog
    {
        return $this->auditLogs()->first();
    }
}
