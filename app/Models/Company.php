<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Company Model (Tenant)
 * 
 * Represents a company/organization in the multi-tenant system.
 * All core data is isolated by company_id.
 */
class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'logo_path',
        'address',
        'phone',
        'email',
        'website',
        'tax_id',
        'bank_name',
        'bank_account_number',
        'bank_swift_code',
        'invoice_terms',
        'base_currency_code',
        'settings',
        'is_active',
        'trial_ends_at',
        'subscription_plan',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            if (empty($company->uuid)) {
                $company->uuid = Str::uuid()->toString();
            }
            if (empty($company->code)) {
                $company->code = strtoupper(Str::slug(Str::limit($company->name, 10, ''), ''));
            }
        });
    }

    // =====================================
    // RELATIONSHIPS
    // =====================================

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    // =====================================
    // SCOPES
    // =====================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =====================================
    // HELPERS
    // =====================================

    /**
     * Get a setting value with dot notation support.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Check if trial has ended.
     */
    public function isTrialExpired(): bool
    {
        if (!$this->trial_ends_at) {
            return false;
        }
        return $this->trial_ends_at->isPast();
    }

    /**
     * Check if company is on a paid plan.
     */
    public function isPaidPlan(): bool
    {
        return in_array($this->subscription_plan, ['pro', 'enterprise']);
    }

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        return asset('storage/' . $this->logo_path);
    }
}
