<?php

namespace Database\Seeders;

use App\Models\RuleProperty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RulePropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RuleProperty::factory(10)->create();
    }
}
