<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class CompanyBankAccount extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'account_name',
        'bank_name',
        'account_number',
        'swift_code',
        'currency_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bank_account_id');
    }

    public function setAsDefault(): void
    {
        // Remove default from other accounts with same currency
        static::where('company_id', $this->company_id)
            ->where('currency_code', $this->currency_code)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        $this->update(['is_default' => true]);
    }
}
