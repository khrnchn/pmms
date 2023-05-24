<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'closed_by',
        'inventory_id',
        'before',
        'restock',
        'sold',
        'damaged',
        'after',
        'cash',
        'online',
        'profit',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
