<?php

namespace Database\Seeders;

use App\Models\FacilityProperty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilityPropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FacilityProperty::factory(10)->create();
    }
}
