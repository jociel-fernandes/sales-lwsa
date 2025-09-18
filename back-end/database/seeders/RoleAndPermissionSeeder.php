<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões
        Permission::create(['name' => 'manage.admin']);
        Permission::create(['name' => 'manage.sales']);
        Permission::create(['name' => 'manage.sellers']);

    $role = Role::firstOrCreate(['name' => 'sellers']);
    // give the correct permission name
    $role->givePermissionTo(['manage.sales']);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());
    }
}
