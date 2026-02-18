<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class CustomsPermit extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'permit_number', 'permit_type',
        'issue_date', 'expiry_date', 'issuing_authority',
        'status', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    const PERMIT_TYPES = [
        'SIUP' => 'Surat Izin Usaha Perdagangan',
        'NIB' => 'Nomor Induk Berusaha',
        'API-U' => 'Angka Pengenal Importir Umum',
        'API-P' => 'Angka Pengenal Importir Produsen',
        'IT' => 'Izin Tipe',
        'PI' => 'Persetujuan Impor',
        'LSPI' => 'Laporan Surveyor Pra-Import',
        'SKI' => 'Surat Keterangan Impor',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date->isBetween(now(), now()->addDays($days));
    }
}
