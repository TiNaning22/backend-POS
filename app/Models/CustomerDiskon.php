<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerDiskon extends Model
{
    protected $fillable = ['customer_id', 'persen_diskon'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}