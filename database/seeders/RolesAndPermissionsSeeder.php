<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Truncate tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

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

            // Category & Tag management
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
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
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',
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
            'view tags',
            'create tags',
            'edit tags',
            'view categories',
            'create categories',
            'edit categories',
        ]);

        // Author
        $author = Role::create(['name' => 'author']);
        $author->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'upload media',
            'view tags',
            'create tags',
            'delete tags',
            'edit tags',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

        ]);

        // Update the existing admin user to super-admin
        \App\Models\User::where('email', 'admin@example.com')
            ->first()
            ?->assignRole('super-admin');
    }
}