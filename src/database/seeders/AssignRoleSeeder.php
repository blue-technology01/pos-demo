<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignRoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::find(1);
        if ($admin) {
            $admin->assignRole('admin');
        }

        $cashier = User::find(2);
        if ($cashier) {
            $cashier->assignRole('cashier');
        }
    }
}
