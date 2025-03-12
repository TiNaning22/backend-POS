<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            'kode_produk' => 'P001',
            'nama_produk' => 'Sabun',
            'harga' => 12422,
            'gambar' => 'ini gambar',
            'barcode' => 'ini barcode',
            'outlet_id' => '1',
            'kategori_id' => '1'
        ]);
    }
}
