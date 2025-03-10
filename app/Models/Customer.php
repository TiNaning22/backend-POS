<?php

namespace App\Models;

use App\Models\Toko;
use App\Models\CustomerDiskon;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['nama_customer', 'toko_id'];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }

    // Relasi dengan CustomerDiscount
    public function discount()
    {
        return $this->hasOne(CustomerDiskon::class, 'customer_id', 'id');
    }

    // Relasi dengan Transactions
    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'customer_id', 'id');
    }
}
