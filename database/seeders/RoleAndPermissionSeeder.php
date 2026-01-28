<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Define permissions
        $permissions = [
            'view users',
            'create user',
            'edit user',
            'delete user',
            'view roles',
            'create role',
            'edit role',
            'delete role',
            'view cash accounts',
            'create cash account',
            'edit cash account',
            'delete cash account',
            'view categories',
            'create category',
            'edit category',
            'delete category',
            'view cash flows',
            'create cash flow',
            'edit cash flow',
            'delete cash flow',
            'view transactions',
            'create transaction',
            'edit transaction',
            'delete transaction',
            'view budgets',
            'create budget',
            'edit budget',
            'delete budget',
            'view reports',
            'view event projects',
            'create event project',
            'edit event project',
            'delete event project',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $verificatorRole = Role::firstOrCreate(['name' => 'verificator']);
        $verificatorRole->syncPermissions([
            'view users',
            'view roles',
            'view cash accounts',
            'view categories',
            'view cash flows',
            'view transactions',
            'view budgets',
            'view reports',
            'view event projects',
        ]);

        $userRole = Role::firstOrCreate(['name' => 'member']);
        $userRole->syncPermissions([
            'view cash accounts',
            'create cash account',
            'edit cash account',
            'view categories',
            'create category',
            'edit category',
            'view cash flows',
            'create cash flow',
            'edit cash flow',
            'view transactions',
            'create transaction',
            'edit transaction',
            'view budgets',
            'create budget',
            'edit budget',
            'view event projects',
            'create event project',
            'edit event project',
        ]);
    }
}
