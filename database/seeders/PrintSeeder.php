<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PrintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('printers')->insert([
            'nama_printer' => 'Printer A',
            'connection_type' => 'bluetooth',
            'printer_type' => 'thermal',
            'deskripsi' => 'Ini Deskripsi',
            'is_active' => true,
        ]);
    }
}
