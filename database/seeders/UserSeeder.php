<?php

namespace Database\Seeders;

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
        ]);

        User::factory()->create([
            'name' => 'User2',
            'email' => 'user2@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@alam-aldyara.com',
        ]);
    }
}
