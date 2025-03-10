<?php

namespace App\Models;

use App\Models\User;
use App\Models\Customer;
use App\Models\Products;
use App\Models\ProdukToko;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $fillable = ['nama_toko', 'alamat'];

    public function users()
    {
        return $this->hasMany(User::class, 'toko_id', 'id');
    }

    // Relasi dengan Products
    public function products()
    {
        return $this->hasMany(Products::class, 'toko_id', 'id');
    }

    // Relasi dengan Product_Toko
    public function productToko()
    {
        return $this->hasMany(ProdukToko::class, 'toko_id', 'id');
    }

    // Relasi dengan Customers
    public function customers()
    {
        return $this->hasMany(Customer::class, 'toko_id', 'id');
    }

    // Relasi dengan Transactions
    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'toko_id', 'id');
    }

}
