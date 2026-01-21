<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * TenantScopedExists - Validates that an ID exists AND belongs to current tenant.
 * 
 * Prevents cross-tenant data injection attacks where a user sends
 * an ID from another company.
 * 
 * Usage:
 *   'category_id' => ['required', new TenantScopedExists('categories')]
 *   'warehouse_id' => ['required', new TenantScopedExists('warehouses', 'id', 'warehouse_id')]
 */
class TenantScopedExists implements ValidationRule
{
    protected string $table;
    protected string $column;
    protected ?string $companyColumn;

    public function __construct(
        string $table,
        string $column = 'id',
        ?string $companyColumn = 'company_id'
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->companyColumn = $companyColumn;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!auth()->check() || !auth()->user()->company_id) {
            $fail('Authentication required for scoped validation.');
            return;
        }

        $companyId = auth()->user()->company_id;

        $exists = DB::table($this->table)
            ->where($this->column, $value)
            ->where($this->companyColumn, $companyId)
            ->exists();

        if (!$exists) {
            $fail("The selected :attribute does not exist or does not belong to your company.");
        }
    }
}
