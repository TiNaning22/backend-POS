<?php

namespace App\Models;

use App\Models\Toko;
use App\Models\User;
use App\Models\Customer;
use App\Models\TransactionItems;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $fillable = ['customer_id', 'user_id', 'toko_id', 'total', 'nomor_invoice'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relasi dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }

    // Relasi dengan Transaction_Items
    public function items()
    {
        return $this->hasMany(TransactionItems::class, 'transaction_id', 'id');
    }
}
