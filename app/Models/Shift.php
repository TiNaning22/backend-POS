<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{

    // use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'date',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
