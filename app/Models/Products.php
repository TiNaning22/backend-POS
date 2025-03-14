<?php

namespace App\Models;

use App\Models\Outlet;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\ProdukOutlet;
use App\Models\TransactionItems;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = ['kode_produk', 'nama_produk', 'harga', 'stock', 'gambar', 'barcode', 'outlet_id', 'kategori_id'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'kategori_id', 'id');
    }

    // Relasi dengan outlet
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id', 'id');
    }

    // Relasi dengan Product_outlet
    public function productOutlet()
    {
        return $this->hasMany(ProdukOutlet::class, 'product_id', 'id');
    }

    // Relasi dengan Transaction_Items
    public function transactionItems()
    {
        return $this->hasMany(TransactionItems::class, 'product_id', 'id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'product_id', 'id');
    }

    public function latestInventory()
    {
        return $this->hasOne(Inventory::class, 'product_id')->latest('tanggal');
    }
}
