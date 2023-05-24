<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'qty',
        'unit_price',
        'total'
    ];

    /**
     * @var string
     */
    protected $table = 'sale_inventories';
}
