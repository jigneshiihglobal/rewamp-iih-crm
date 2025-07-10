<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        // Permission::create(['name' => 'admin.*']);
        // Permission::create(['name' => 'user.*']);

        // create roles and assign created permissions
        $roleAdmin = Role::create(['name' => 'Admin', 'id' => 1]);
        // $roleAdmin->givePermissionTo('admin.*');
        // $roleAdmin->givePermissionTo('user.*');

        $roleUser = Role::create(['name' => 'User', 'id' => 2]);
        // $roleUser->givePermissionTo('user.*');
    }
}
