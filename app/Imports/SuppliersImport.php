<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SuppliersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Supplier([
            'name' => $row['name'],
            'code' => $row['code'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'contact_person' => $row['contact_person'] ?? null,
            'status' => true, // Default to active
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'nullable|email',
        ];
    }
}
