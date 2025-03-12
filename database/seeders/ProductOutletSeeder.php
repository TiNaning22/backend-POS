<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductOutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('produk_outlets')->insert([
            'product_id' => '1',
            'outlet_id' => '1',
            'harga_beli' => '1000',
            'harga_jual' => '1500'
        ]);
    }
}
