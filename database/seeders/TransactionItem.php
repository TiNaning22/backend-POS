<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TransactionItem extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transaction_items')->insert([
            'transaction_id' => '1',
            'product_id' => '1',
            'quantity' => '2',
            'harga' => '12000',
        ]);
    }
}
