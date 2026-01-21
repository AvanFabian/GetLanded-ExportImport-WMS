<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory, BelongsToTenant;
    use LogsActivity;

    protected $fillable = ['warehouse_id', 'transaction_code', 'date', 'supplier_id', 'total', 'notes'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(StockInDetail::class);
    }
}
