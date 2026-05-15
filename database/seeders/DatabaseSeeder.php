<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tài khoản demo 1
        User::updateOrCreate(['email' => 'demo@example.com'], [
            'name'         => 'Demo User',
            'password'     => Hash::make('123456'),
            'is_activated' => true,
        ]);

        // Tài khoản demo 2
        User::updateOrCreate(['email' => 'demo2@example.com'], [
            'name'         => 'Demo User 2',
            'password'     => Hash::make('123456'),
            'is_activated' => true,
        ]);
    }
}
