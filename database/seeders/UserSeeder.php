<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
            'role' => 'admin',
            'outlet_id' => '1'
       ]);

       DB::table('users')->insert([
            'name' => 'kasir',
            'email' => 'kasir@example.com',
            'password' => bcrypt('kasir'),
            'role' => 'kasir',
            'outlet_id' => '1'
       ]);
    }
}
