<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Administrator'
        ]);

        DB::table('roles')->insert([
            'id' => 2,
            'name' => 'Team leader'
        ]);

        DB::table('roles')->insert([
            'id' => 3,
            'name' => 'User'
        ]);
    }
}
