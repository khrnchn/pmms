<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'sales';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'total_price',
        'status'
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(SaleInventory::class, 'sales_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
