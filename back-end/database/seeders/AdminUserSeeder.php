<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create admin role and give all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Create or retrieve the admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@test.io'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        // Assign admin role if not already assigned
        if (! $user->hasRole('admin')) {
            $user->assignRole($adminRole);
        }
    }
}
