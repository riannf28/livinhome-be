<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('list_rules')->insert([
            ['name' => 'Dilarang membawa lawan jenis'],
            ['name' => 'Boleh pasutri'],
            ['name' => 'Boleh bawa hewan'],
            ['name' => 'Boleh bawa anak'],
            ['name' => 'Maks. 1 orang/kamar'],
            ['name' => 'Maks. 2 orang/kamar'],
            ['name' => 'Maks. 3 orang/kamar'],
            ['name' => 'Ada jam malam untuk tamu'],
            ['name' => 'Denda kerusakan properti'],
            ['name' => 'Dilarang membawa hewan'], // Duplikasi, bisa dihapus jika tidak ingin redundan
            ['name' => 'Dilarang menerima tamu'],
            ['name' => 'Dilarang merokok di kamar'],
            ['name' => 'Khusus Mahasiswa'],
            ['name' => 'Khusus Karyawan'],
            ['name' => 'Pasutri wajib membawa surat nikah'],
            ['name' => 'Termasuk listrik'],
            ['name' => 'Termasuk wifi'],
        ]);
    }
}
