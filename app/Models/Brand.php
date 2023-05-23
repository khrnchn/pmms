<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'website',
        'is_visible'
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
