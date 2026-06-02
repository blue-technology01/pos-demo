<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles & permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'dam',
            'email' => 'dam@example.com',
            'password' => Hash::make('123456'),
        ]);

        $cashier = User::factory()->create([
            'name' => 'cashier',
            'email' => 'cashier@example.com',
            'password' => Hash::make('123456'),
        ]);

        // Assign roles directly
        $admin->assignRole('admin');
        $cashier->assignRole('cashier');
    }
}
