<?php

namespace App\Models;

use App\Models\Outlet;
use App\Models\Products;
use Illuminate\Database\Eloquent\Model;

class ProdukOutlet extends Model
{
    protected $fillable = ['harga_beli', 'harga_jual', 'product_id', 'outlet_id'];

    protected $table = 'produk_outlets';

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }

    // Relasi dengan Toko
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id', 'id');
    }
}
