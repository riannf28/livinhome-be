<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListFacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('list_facilities')->insert([
            ['name' => 'Balkon'],
            ['name' => 'Dapur'],
            ['name' => 'Garasi'],
            ['name' => 'Gudang'],
            ['name' => 'Jemuran'],
            ['name' => 'Ruang Keluarga'],
            ['name' => 'Ruang Makan'],
            ['name' => 'Ruang Tamu'],
        ]);
    }
}
