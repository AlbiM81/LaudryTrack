<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::findOrCreate('PEMILIK', 'web');
        Role::findOrCreate('KASIR', 'web');

        $owner = User::updateOrCreate(
            ['username' => 'pemilik'],
            [
                'name' => 'Pemilik Laundry',
                'email' => 'pemilik@laundrytrack.local',
                'password' => Hash::make('password123'),
            ]
        );
        $owner->syncRoles([$ownerRole]);

        $cashier = User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir Shift 1',
                'email' => 'kasir@laundrytrack.local',
                'password' => Hash::make('password123'),
            ]
        );
        $cashier->syncRoles(['KASIR']);
    }
}
