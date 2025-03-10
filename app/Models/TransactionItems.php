<?php

namespace App\Models;

use App\Models\Products;
use App\Models\Transactions;
use Illuminate\Database\Eloquent\Model;

class TransactionItems extends Model
{
    protected $fillable = ['transaction_id', 'product_id', 'quantity', 'harga'];

    public function transaction()
    {
        return $this->belongsTo(Transactions::class, 'transaction_id', 'id');
    }

    // Relasi dengan Product
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
