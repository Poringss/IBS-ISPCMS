<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme123')),
                'role' => 'admin',
            ]
        );

        // Sample client
        User::updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Sample Client',
                'password' => Hash::make('client123'),
                'role' => 'client',
            ]
        );
    }
}
