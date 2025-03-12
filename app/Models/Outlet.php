<?php

namespace App\Models;

use App\Models\User;
use App\Models\Customer;
use App\Models\Products;
use App\Models\ProdukOutlet;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $fillable = ['nama_outlet', 'alamat'];

    protected $table = 'outlets';

    public function users()
    {
        return $this->hasMany(User::class, 'outlet_id', 'id');
    }

    // Relasi dengan Products
    public function products()
    {
        return $this->hasMany(Products::class, 'outlet_id', 'id');
    }

    // Relasi dengan Product_outlet
    public function productOutlet()
    {
        return $this->hasMany(ProdukOutlet::class, 'outlet_id', 'id');
    }

    // Relasi dengan Customers
    public function customers()
    {
        return $this->hasMany(Customer::class, 'outlet_id', 'id');
    }

    // Relasi dengan Transactions
    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'outlet_id', 'id');
    }

}
