<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AuditLog Model
 * 
 * Stores all auditable events (create, update, delete, forceDelete)
 * across the system using polymorphic relationships.
 * 
 * Audit records persist even after the auditable entity is deleted.
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $event
 * @property string $auditable_type
 * @property int $auditable_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $url
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 */
class AuditLog extends Model
{
    /**
     * Audit logs are immutable - no updated_at
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'snapshot_data',
        'url',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'snapshot_data' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Event types
     */
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_FORCE_DELETED = 'forceDeleted';

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic).
     * Note: May return null if the model was hard-deleted.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by auditable model type.
     */
    public function scopeForModel($query, string $modelClass)
    {
        return $query->where('auditable_type', $modelClass);
    }

    /**
     * Scope to filter by specific model instance.
     */
    public function scopeForModelInstance($query, Model $model)
    {
        return $query->where('auditable_type', get_class($model))
                     ->where('auditable_id', $model->getKey());
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get a human-readable description of the event.
     */
    public function getDescriptionAttribute(): string
    {
        $modelName = class_basename($this->auditable_type);
        
        return match ($this->event) {
            self::EVENT_CREATED => "{$modelName} #{$this->auditable_id} was created",
            self::EVENT_UPDATED => "{$modelName} #{$this->auditable_id} was updated",
            self::EVENT_DELETED => "{$modelName} #{$this->auditable_id} was deleted",
            self::EVENT_FORCE_DELETED => "{$modelName} #{$this->auditable_id} was permanently deleted",
            default => "{$modelName} #{$this->auditable_id}: {$this->event}",
        };
    }

    /**
     * Get the list of changed attributes.
     */
    public function getChangedAttributesAttribute(): array
    {
        if ($this->event === self::EVENT_CREATED) {
            return array_keys($this->new_values ?? []);
        }
        
        return array_keys($this->old_values ?? []);
    }
}
