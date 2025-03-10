<?php

namespace App\Models;

use App\Models\Products;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['nama_kategori'];

    public function products()
    {
        return $this->hasMany(Products::class, 'kategori_id', 'id');
    }
}
