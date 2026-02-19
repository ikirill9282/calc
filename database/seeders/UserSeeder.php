<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Администратор',
                'phone' => '79999999999',
                'password' => 'Admin123',
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );
    }
}
