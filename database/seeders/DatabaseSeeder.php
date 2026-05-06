<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'slug' => 'admin',
                'guard_name' => 'web',
                'description' => 'Full system administrator.',
            ]
        );

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
                'status' => 'active',
            ]
        );

        $admin->update(['role_id' => $adminRole->id]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
