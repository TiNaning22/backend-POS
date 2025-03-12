<?php

namespace App\Models;

use App\Models\Outlet;
use App\Models\CustomerDiskon;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['nama_customer', 'outlet_id'];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id', 'id');
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
