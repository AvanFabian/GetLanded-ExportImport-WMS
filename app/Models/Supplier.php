<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = ['company_id', 'name', 'address', 'latitude', 'longitude', 'phone', 'email', 'contact_person'];

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    /**
     * Boot method to handle auto-geocoding.
     */
    protected static function booted(): void
    {
        static::saving(function (Supplier $supplier) {
            // Only geocode if address changed or coords missing, and address exists
            if (($supplier->isDirty('address') || empty($supplier->latitude)) && !empty($supplier->address)) {
                $service = new \App\Services\GeocodingService();
                $coords = $service->getCoordinates($supplier->address);

                if ($coords) {
                    $supplier->latitude = $coords['lat'];
                    $supplier->longitude = $coords['lon'];
                }
            }
        });
    }
}
