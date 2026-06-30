<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@baano.local',
            'phone' => '+79000000000',
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'is_admin' => true,
            'password' => Hash::make('password123'),
        ]);

        $this->command->info('Админ создан: admin@baano.local / password123');
    }
}