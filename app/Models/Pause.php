<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pause extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'reason',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
