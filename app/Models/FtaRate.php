<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FtaRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'fta_scheme_id',
        'hs_code',
        'rate',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
    ];

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(FtaScheme::class, 'fta_scheme_id');
    }
}
