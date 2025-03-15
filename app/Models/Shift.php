<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{

    // use HasFactory;

    protected $fillable = [
        'user_id',
        'jadwal',
        'date',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
