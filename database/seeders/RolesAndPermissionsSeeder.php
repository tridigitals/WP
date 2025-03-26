<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // Permission management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            
            // Content management
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            
            // Media management
            'upload media',
            'delete media',
            
            // Comment management
            'moderate comments',
            'delete comments',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view roles',
            'view permissions',
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'upload media',
            'delete media',
            'moderate comments',
            'delete comments',
        ]);

        // Editor
        $editor = Role::create(['name' => 'editor']);
        $editor->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'publish posts',
            'upload media',
            'moderate comments',
        ]);

        // Author
        $author = Role::create(['name' => 'author']);
        $author->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'upload media',
        ]);

        // Update the existing admin user to super-admin
        \App\Models\User::where('email', 'admin@example.com')
            ->first()
            ?->assignRole('super-admin');
    }
}