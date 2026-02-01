<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Customer([
            'name' => $row['name'],
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'city' => $row['city'] ?? null,
            'zip_code' => $row['zip_code'] ?? null,
            'tax_id' => $row['tax_id'] ?? null,
            
            // Set default active status
            'is_active' => true,
            
            // Track creator (system import = user 1 or current auth)
            'created_by' => Auth::id() ?? 1,
            'updated_by' => Auth::id() ?? 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:customers,name',
            'email' => 'nullable|email',
        ];
    }
}
