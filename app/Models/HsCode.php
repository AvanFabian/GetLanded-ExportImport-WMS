<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'bm_rate',
        'ppn_rate',
        'pph_api_rate',
        'pph_non_api_rate',
    ];

    protected $casts = [
        'bm_rate' => 'decimal:2',
        'ppn_rate' => 'decimal:2',
        'pph_api_rate' => 'decimal:2',
        'pph_non_api_rate' => 'decimal:2',
    ];
}
