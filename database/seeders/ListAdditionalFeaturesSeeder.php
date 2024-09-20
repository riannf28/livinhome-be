<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListAdditionalFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('list_additional_features')->insert([
            [
                'nama' => 'LivinClean',
                'deskripsi' => 'Jasa Kebersihan',
                'harga' => 80000
            ],
            [
                'nama' => 'LivinFix',
                'deskripsi' => 'Jasa Perbaikan Rumah',
                'harga' => 100000
            ],
            [
                'nama' => 'Pemasangan AC',
                'deskripsi' => 'Service tambahan',
                'harga' => 150000
            ],
            [
                'nama' => 'Pemasangan Wifi',
                'deskripsi' => 'Service tambahan',
                'harga' => 50000
            ],
        ]);
    }
}
