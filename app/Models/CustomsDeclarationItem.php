<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomsDeclarationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customs_declaration_id', 'product_id', 'hs_code',
        'description', 'quantity', 'unit_of_measure',
        'unit_value', 'total_value', 'country_of_origin',
    ];

    protected $casts = [
        'unit_value' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(CustomsDeclaration::class, 'customs_declaration_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
