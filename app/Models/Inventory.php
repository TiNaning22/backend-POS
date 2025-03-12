<?php

namespace App\Models;

use App\Models\Products;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'tanggal',
        'stok_awal',
        'stok_masuk',
        'stok_keluar', 
        'stok_akhir',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    // Relasi dengan model Product
    public function product()
    {
    return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
