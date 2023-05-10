<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'end',
        'endTime',
        'isAllDay',
        'organizer',
        'start',
        'startTime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
