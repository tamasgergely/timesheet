<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\Role::create(['name' => 'Administrator']);
        \App\Models\Role::create(['name' => 'Team Leader']);
        \App\Models\Role::create(['name' => 'User']);

        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => '2023-07-10 12:07:43',
            'role_id' => 1
        ]);
    }
}