<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Kategori;
use Illuminate\Database\Seeder;
use Database\Seeders\OutletSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ProductSeed;
use Database\Seeders\ShiftSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\TransactionItem;
use Database\Seeders\ProductTokoSeeder;
use Database\Seeders\TransactionsSeeder;
use Database\Seeders\CustomerDiskonSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            Kategori::class,
            OutletSeeder::class,
            ProductSeed::class,
            CustomerSeeder::class,
            UserSeeder::class,
            // TransactionsSeeder::class,
            // TransactionItem::class,
            CustomerDiskonSeeder::class,
            ProductOutletSeeder::class,
            ShiftSeeder::class,
        ]);
    }
}
