<?php

namespace App\Models;

use App\Models\Toko;
use App\Models\Category;
use App\Models\TransactionItems;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = ['kode_produk', 'nama_produk', 'harga', 'stock', 'gambar', 'barcode', 'toko_id'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'kategori_id', 'id');
    }

    // Relasi dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }

    // Relasi dengan Product_Toko
    public function productToko()
    {
        return $this->hasMany(ProductToko::class, 'product_id', 'id');
    }

    // Relasi dengan Transaction_Items
    public function transactionItems()
    {
        return $this->hasMany(TransactionItems::class, 'product_id', 'id');
    }
}
