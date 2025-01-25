<?php

namespace Database\Seeders;

use App\enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'User',
            'email' => 'user@gmail.com',
        ])->assignRole(RolesEnum::User->value);

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
        ])->assignRole(RolesEnum::Admin->value);
    }
}
