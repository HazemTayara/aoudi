<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laratrust\Models\Role;
use Laratrust\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LaratrustSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('Clearing existing permissions and roles...');

        // Clear existing relationships first (order matters!)
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissions = [
            // Settings permissions (super-admin only)
            'view-settings',
            'update-settings',

            // User management permissions (super-admin only)
            'manage-users',

            // City management permissions
            'view-cities',
            'create-cities',
            'edit-cities',
            'delete-cities',
            'force-delete-cities',
            'restore-cities',
            'view-city-orders',

            // Driver management permissions
            'view-drivers',
            'create-drivers',
            'edit-drivers',
            'delete-drivers',
            'force-delete-drivers',
            'restore-drivers',
        ];

        $this->command->info('Creating permissions...');

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'display_name' => ucwords(str_replace('-', ' ', $permission)),
                'description' => 'Can ' . ucwords(str_replace('-', ' ', $permission))
            ]);
        }

        $this->command->info('Creating roles...');

        // Super Admin - gets ALL permissions
        $superAdmin = Role::create([
            'name' => 'super-admin',
            'display_name' => 'Super Administrator',
            'description' => 'Full system access with all permissions'
        ]);
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('Super Admin role created with ' . Permission::count() . ' permissions');

        // Admin - gets everything EXCEPT settings and user management
        $adminPermissions = array_diff($permissions, ['view-settings', 'update-settings', 'manage-users']);
        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrative access without system settings and user management'
        ]);
        $admin->syncPermissions($adminPermissions);
        $this->command->info('Admin role created with ' . count($adminPermissions) . ' permissions');

        // Employee - gets only view permissions
        $employeePermissions = [
            'view-cities',
            'view-city-orders',
            'view-drivers',
        ];
        $employee = Role::create([
            'name' => 'employee',
            'display_name' => 'Employee',
            'description' => 'Basic employee access - view only'
        ]);
        $employee->syncPermissions($employeePermissions);
        $this->command->info('Employee role created with ' . count($employeePermissions) . ' permissions');

        // Assign roles to existing users
        $this->command->info('Assigning roles to users...');

        // Assign super-admin role to hazemtayara36@gmail.com
        $superAdminUser = User::where('email', 'hazemtayara36@gmail.com')->first();
        if ($superAdminUser) {
            $superAdminUser->syncRoles(['super-admin']);
            $this->command->info('✓ Super-admin role assigned to ' . $superAdminUser->email);
        } else {
            $this->command->warn('✗ User hazemtayara36@gmail.com not found - creating now...');
            $superAdminUser = User::create([
                'name' => 'Hazem Tayara',
                'email' => 'hazemtayara36@gmail.com',
                'password' => bcrypt('password123')
            ]);
            $superAdminUser->syncRoles(['super-admin']);
            $this->command->info('✓ User created and Super-admin role assigned');
        }

        // Assign admin role to etwase59@gmail.com
        $adminUser = User::where('email', 'etwase59@gmail.com')->first();
        if ($adminUser) {
            $adminUser->syncRoles(['admin']);
            $this->command->info('✓ Admin role assigned to ' . $adminUser->email);
        } else {
            $this->command->warn('✗ User etwase59@gmail.com not found - creating now...');
            $adminUser = User::create([
                'name' => 'Omar Tayara',
                'email' => 'etwase59@gmail.com',
                'password' => bcrypt('password123')
            ]);
            $adminUser->syncRoles(['admin']);
            $this->command->info('✓ User created and Admin role assigned');
        }

        // Assign employee role to tchat8288@gmail.com
        $employeeUser = User::where('email', 'tchat8288@gmail.com')->first();
        if ($employeeUser) {
            $employeeUser->syncRoles(['employee']);
            $this->command->info('✓ Employee role assigned to ' . $employeeUser->email);
        } else {
            $this->command->warn('✗ User tchat8288@gmail.com not found - creating now...');
            $employeeUser = User::create([
                'name' => 'Khaled Tayara',
                'email' => 'tchat8288@gmail.com',
                'password' => bcrypt('password123')
            ]);
            $employeeUser->syncRoles(['employee']);
            $this->command->info('✓ User created and Employee role assigned');
        }

        $this->command->info('==============================================');
        $this->command->info('Laratrust Seeder Completed Successfully!');
        $this->command->info('==============================================');
        $this->command->info('Roles created: super-admin, admin, employee');
        $this->command->info('Total permissions: ' . count($permissions));
        $this->command->info('==============================================');

        // Display permission summary by role
        $this->command->info("\nPermission Summary:");
        $this->command->info("Super Admin: " . Permission::count() . " permissions");
        $this->command->info("Admin: " . count($adminPermissions) . " permissions");
        $this->command->info("Employee: " . count($employeePermissions) . " permissions");
        $this->command->info("==============================================\n");

        // Show login credentials
        $this->command->info("\nDemo Login Credentials:");
        $this->command->info("Super Admin: hazemtayara36@gmail.com / password123");
        $this->command->info("Admin: etwase59@gmail.com / password123");
        $this->command->info("Employee: tchat8288@gmail.com / password123");
        $this->command->info("==============================================\n");
    }
}