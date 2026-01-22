<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class SecurityLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'user_id',
        'event',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    const EVENT_LOGIN = 'login';
    const EVENT_LOGOUT = 'logout';
    const EVENT_FAILED_LOGIN = 'failed_login';
    const EVENT_PASSWORD_CHANGED = 'password_changed';
    const EVENT_2FA_ENABLED = '2fa_enabled';
    const EVENT_2FA_DISABLED = '2fa_disabled';
    const EVENT_SETTINGS_CHANGED = 'settings_changed';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $event, ?int $userId = null, array $metadata = []): self
    {
        return static::create([
            'company_id' => auth()->user()?->company_id,
            'user_id' => $userId ?? auth()->id(),
            'event' => $event,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
