<?php

namespace App\Models;

use App\Models\Toko;
use App\Models\Products;
use Illuminate\Database\Eloquent\Model;

class ProdukToko extends Model
{
    protected $fillable = ['harga_beli', 'harga_jual', 'product_id', 'toko_id'];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }

    // Relasi dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }
}
