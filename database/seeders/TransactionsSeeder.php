<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transactions')->insert([
            'customer_id' => '1',
            'user_id' => '1',
            'toko_id' => '1',
            'total' => 21321,
            'nomor_invoice' => 'INV/21/21/2',
        ]);
    }
}
