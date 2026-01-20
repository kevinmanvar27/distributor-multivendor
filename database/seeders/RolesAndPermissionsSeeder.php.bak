<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Remove all existing role-permission associations first
        DB::table('permission_role')->delete();
        
        // Remove all existing permissions
        Permission::truncate();
        
        // Remove all existing roles
        Role::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create default roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full access to all system features'
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Administrative access to most system features'
            ],
            [
                'name' => 'editor',
                'display_name' => 'Editor',
                'description' => 'Can manage content and users'
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Regular user with limited access'
            ]
        ];

        // Create roles
        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        // Create new permissions based on requirements
        $permissions = [
            // Staff Management Permissions
            [
                'name' => 'show_staff',
                'display_name' => 'Show Staff',
                'description' => 'View staff members'
            ],
            [
                'name' => 'add_staff',
                'display_name' => 'Add Staff',
                'description' => 'Create new staff members'
            ],
            [
                'name' => 'edit_staff',
                'display_name' => 'Edit Staff',
                'description' => 'Modify existing staff members'
            ],
            [
                'name' => 'delete_staff',
                'display_name' => 'Delete Staff',
                'description' => 'Remove staff members'
            ],
            
            // User Management Permissions
            [
                'name' => 'show_user',
                'display_name' => 'Show User',
                'description' => 'View users'
            ],
            [
                'name' => 'add_user',
                'display_name' => 'Add User',
                'description' => 'Create new users'
            ],
            [
                'name' => 'edit_user',
                'display_name' => 'Edit User',
                'description' => 'Modify existing users'
            ],
            [
                'name' => 'delete_user',
                'display_name' => 'Delete User',
                'description' => 'Remove users'
            ],
            
            // User Role & Permissions
            [
                'name' => 'show_user_role_permission',
                'display_name' => 'Show User Roles & Permissions',
                'description' => 'View user roles and permissions'
            ],
            [
                'name' => 'add_user_role_permission',
                'display_name' => 'Add User Roles & Permissions',
                'description' => 'Create new user roles and permissions'
            ],
            [
                'name' => 'edit_user_role_permission',
                'display_name' => 'Edit User Roles & Permissions',
                'description' => 'Modify existing user roles and permissions'
            ],
            [
                'name' => 'delete_user_role_permission',
                'display_name' => 'Delete User Roles & Permissions',
                'description' => 'Remove user roles and permissions'
            ],
            
            // Manage Roles & Permissions (for route middleware)
            [
                'name' => 'manage_roles',
                'display_name' => 'Manage Roles & Permissions',
                'description' => 'Full access to role and permission management'
            ],
            
            // Settings
            [
                'name' => 'manage_settings',
                'display_name' => 'Manage Settings',
                'description' => 'Access and modify application settings'
            ],
            
            // Category Management Permissions
            [
                'name' => 'viewAny_category',
                'display_name' => 'View Any Category',
                'description' => 'View all categories'
            ],
            [
                'name' => 'view_category',
                'display_name' => 'View Category',
                'description' => 'View a specific category'
            ],
            [
                'name' => 'create_category',
                'display_name' => 'Create Category',
                'description' => 'Create new categories'
            ],
            [
                'name' => 'update_category',
                'display_name' => 'Update Category',
                'description' => 'Modify existing categories'
            ],
            [
                'name' => 'delete_category',
                'display_name' => 'Delete Category',
                'description' => 'Remove categories'
            ],

            // Product Management Permissions
            [
                'name' => 'viewAny_product',
                'display_name' => 'View Any Product',
                'description' => 'View all products'
            ],
            [
                'name' => 'view_product',
                'display_name' => 'View Product',
                'description' => 'View a specific product'
            ],
            [
                'name' => 'create_product',
                'display_name' => 'Create Product',
                'description' => 'Create new products'
            ],
            [
                'name' => 'update_product',
                'display_name' => 'Update Product',
                'description' => 'Modify existing products'
            ],
            [
                'name' => 'delete_product',
                'display_name' => 'Delete Product',
                'description' => 'Remove products'
            ],
            
            // Media Management Permissions
            [
                'name' => 'viewAny_media',
                'display_name' => 'View Any Media',
                'description' => 'View all media files'
            ],
            [
                'name' => 'view_media',
                'display_name' => 'View Media',
                'description' => 'View a specific media file'
            ],
            [
                'name' => 'create_media',
                'display_name' => 'Create Media',
                'description' => 'Upload new media files'
            ],
            [
                'name' => 'update_media',
                'display_name' => 'Update Media',
                'description' => 'Modify existing media files'
            ],
            [
                'name' => 'delete_media',
                'display_name' => 'Delete Media',
                'description' => 'Remove media files'
            ],
            
            // Proforma Invoice Management Permissions
            [
                'name' => 'manage_proforma_invoices',
                'display_name' => 'Manage Proforma Invoices',
                'description' => 'Access and manage proforma invoices'
            ],
            
            // Page Management Permissions
            [
                'name' => 'viewAny_page',
                'display_name' => 'View Any Page',
                'description' => 'View all pages'
            ],
            [
                'name' => 'view_page',
                'display_name' => 'View Page',
                'description' => 'View a specific page'
            ],
            [
                'name' => 'create_page',
                'display_name' => 'Create Page',
                'description' => 'Create new pages'
            ],
            [
                'name' => 'update_page',
                'display_name' => 'Update Page',
                'description' => 'Modify existing pages'
            ],
            [
                'name' => 'delete_page',
                'display_name' => 'Delete Page',
                'description' => 'Remove pages'
            ],
        ];

        // Create new permissions
        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Get all permissions for super admin
        $allPermissions = Permission::all();
        
        // Update roles with new permissions
        $roles = Role::all();
        foreach ($roles as $role) {
            if ($role->name === 'super_admin') {
                // Super admin gets all permissions
                $role->permissions()->sync($allPermissions);
            } elseif ($role->name === 'admin') {
                // Admin gets all permissions for now (can be customized)
                $role->permissions()->sync($allPermissions);
            } elseif ($role->name === 'editor') {
                // Editor gets role and permission management permissions
                $editorPermissions = Permission::whereIn('name', [
                    'show_user_role_permission',
                    'add_user_role_permission',
                    'edit_user_role_permission',
                    'delete_user_role_permission',
                    'manage_roles',
                    'viewAny_product',
                    'view_product',
                    'create_product',
                    'update_product',
                    'viewAny_media',
                    'view_media',
                    'create_media',
                    'update_media',
                    'viewAny_page',
                    'view_page',
                    'create_page',
                    'update_page'
                ])->get();
                $role->permissions()->sync($editorPermissions);
            } else {
                // Other roles get no permissions by default
                $role->permissions()->detach();
            }
        }
        
        // Assign roles to existing users based on their user_role field
        $users = User::all();
        foreach ($users as $user) {
            // Skip if user already has roles assigned through the new system
            if ($user->roles()->count() > 0) {
                continue;
            }
            
            // Assign role based on user_role field
            if ($user->user_role) {
                $role = Role::where('name', $user->user_role)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }
            }
        }
    }
}