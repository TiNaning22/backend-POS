<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Toko;
use App\Models\Shift;
use App\Models\Transactions;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;

    // const ROLE_SUPERADMIN = 'superadmin';
    // const ROLE_ADMIN = 'admin';
    // const ROLE_KASIR = 'kasir';

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isKasir()
    {
        return $this->role === 'kasir';
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'toko_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }

    // Relasi dengan Transactions
    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'user_id', 'id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function activeShift()
    {
        $now = now();
        $today = $now->format('Y-m-d');
        
        return $this->shifts()
            ->where('date', $today)
            ->where('is_active', true)
            ->where('start_time', '<=', $now->format('H:i:s'))
            ->where('end_time', '>=', $now->format('H:i:s'))
            ->first();
    }
}
