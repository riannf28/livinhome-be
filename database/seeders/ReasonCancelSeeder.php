<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReasonCancelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reason_cancels')->insert([
            [
                'reason' => 'Ingin mengubah waktu survei',
            ],
            [
                'reason' => 'Sudah mendapatkan properti lain',
            ],
            [
                'reason' => 'Lainnya/berubah pikiran',
            ],
        ]);
    }
}
