<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * TenantScopedUnique - Validates uniqueness within current tenant only.
 * 
 * Usage:
 *   'code' => ['required', new TenantScopedUnique('products', 'code')]
 *   'code' => ['required', new TenantScopedUnique('products', 'code', $product->id)]
 */
class TenantScopedUnique implements ValidationRule
{
    protected string $table;
    protected string $column;
    protected ?int $ignoreId;
    protected ?string $companyColumn;

    public function __construct(
        string $table,
        string $column = 'code',
        ?int $ignoreId = null,
        ?string $companyColumn = 'company_id'
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
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

        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where($this->companyColumn, $companyId);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The :attribute has already been taken within your company.");
        }
    }
}
