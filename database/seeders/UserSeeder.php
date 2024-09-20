<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'fullname' => 'admin',
                'gender' => true,
                'date_of_birth' => '2002-10-10',
                'phone_number' => '6281216913886',
                'roles' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin')
            ],
            [
                'fullname' => 'renter',
                'gender' => true,
                'date_of_birth' => '2002-10-10',
                'phone_number' => '6281274377477',
                'roles' => 'renter',
                'email' => 'renter@renter.com',
                'password' => Hash::make('renter')
            ],
            [
                'fullname' => 'owner',
                'gender' => true,
                'date_of_birth' => '2002-10-10',
                'phone_number' => '628492992929',
                'roles' => 'owner',
                'email' => 'owner@owner.com',
                'password' => Hash::make('owner')
            ],
        ]);
    }
}
