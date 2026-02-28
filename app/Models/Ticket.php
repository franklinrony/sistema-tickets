<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'prefix_id',
        'priority',
        'status',
        'assigned_to',
        'attended_at',
        'completed_at',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class , 'assigned_to');
    }
}
