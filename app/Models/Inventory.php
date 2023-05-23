<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
        'sku',
        'security_stock',
        'is_visible',
        'old_price',
        'price',
        'cost'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
