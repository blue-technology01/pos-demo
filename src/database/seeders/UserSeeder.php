<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'admin',
                'phone' => '000000000',
                'password' => Hash::make('123456'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'cashier',
                'phone' => '000000001',
                'password' => Hash::make('123456'),
            ]
        );
    }
}
