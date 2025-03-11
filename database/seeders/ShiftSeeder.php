<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shifts')->insert([
            'user_id' => '1',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'date' => '2025-03-11',
            'is_active' => true
        ]);
    }
}
