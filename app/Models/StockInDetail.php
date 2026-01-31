<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInDetail extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['stock_in_id', 'product_id', 'quantity', 'purchase_price', 'allocated_landed_cost', 'total'];

    public function getFinalCostAttribute()
    {
        return $this->purchase_price + $this->allocated_landed_cost;
    }

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
