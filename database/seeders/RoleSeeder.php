<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Full system access with all permissions',
            ],
            [
                'name' => 'manager',
                'description' => 'Hotel manager with operational control',
            ],
            [
                'name' => 'receptionist',
                'description' => 'Front desk operations and guest management',
            ],
            [
                'name' => 'housekeeping',
                'description' => 'Room management and cleaning status',
            ],
            [
                'name' => 'accountant',
                'description' => 'Financial operations and reporting',
            ],
            [
                'name' => 'supervisor',
                'description' => 'Oversight and reporting with limited management access',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                array_merge($roleData, ['slug' => Str::slug($roleData['name']), 'guard_name' => 'web'])
            );
        }

        // Get admin role and assign all permissions
        $adminRole = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);

        // Assign specific permissions to other roles
        $this->assignManagerPermissions();
        $this->assignReceptionistPermissions();
        $this->assignHousekeepingPermissions();
        $this->assignAccountantPermissions();
        $this->assignSupervisorPermissions();
    }

    private function assignManagerPermissions(): void
    {
        $managerRole = Role::where('name', 'manager')->first();
        
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Bookings
            'bookings.view',
            'bookings.create',
            'bookings.edit',
            'bookings.cancel',
            'bookings.checkin',
            'bookings.checkout',
            
            // Rooms
            'rooms.view',
            'rooms.create',
            'rooms.edit',
            'rooms.delete',
            
            // Guests
            'guests.view',
            'guests.create',
            'guests.edit',
            'guests.delete',
            
            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.cancel',
            
            // Payments
            'payments.view',
            'payments.create',
            'payments.refund',
            
            // Reports
            'reports.view',
            'reports.export',
            
            // Services
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',
            
            // Settings
            'settings.view',
            'settings.update',

            // Audit
            'audit.view',
        ];

        $permissionModels = Permission::whereIn('name', $permissions)->get();
        $managerRole->syncPermissions($permissionModels);
    }

    private function assignReceptionistPermissions(): void
    {
        $receptionistRole = Role::where('name', 'receptionist')->first();
        
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Bookings
            'bookings.view',
            'bookings.create',
            'bookings.edit',
            'bookings.cancel',
            'bookings.checkin',
            'bookings.checkout',
            
            // Rooms
            'rooms.view',
            'rooms.update_status',
            
            // Guests
            'guests.view',
            'guests.create',
            'guests.edit',
            
            // Invoices
            'invoices.view',
            'invoices.create',
            
            // Payments
            'payments.view',
            'payments.create',
        ];

        $permissionModels = Permission::whereIn('name', $permissions)->get();
        $receptionistRole->syncPermissions($permissionModels);
    }

    private function assignHousekeepingPermissions(): void
    {
        $housekeepingRole = Role::where('name', 'housekeeping')->first();
        
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Rooms
            'rooms.view',
            'rooms.update_status',
            'rooms.update_cleaning_status',
        ];

        $permissionModels = Permission::whereIn('name', $permissions)->get();
        $housekeepingRole->syncPermissions($permissionModels);
    }

    private function assignAccountantPermissions(): void
    {
        $accountantRole = Role::where('name', 'accountant')->first();
        
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.cancel',
            
            // Payments
            'payments.view',
            'payments.create',
            'payments.refund',
            
            // Reports
            'reports.view',
            'reports.export',
            
            // Bookings (view only)
            'bookings.view',
            
            // Guests (view only)
            'guests.view',
        ];

        $permissionModels = Permission::whereIn('name', $permissions)->get();
        $accountantRole->syncPermissions($permissionModels);
    }

    private function assignSupervisorPermissions(): void
    {
        $supervisorRole = Role::where('name', 'supervisor')->first();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Bookings
            'bookings.view',
            'bookings.edit',
            'bookings.cancel',

            // Rooms
            'rooms.view',
            'rooms.edit',

            // Guests
            'guests.view',
            'guests.edit',

            // Reports
            'reports.view',

            // Audit
            'audit.view',
        ];

        $permissionModels = Permission::whereIn('name', $permissions)->get();
        $supervisorRole->syncPermissions($permissionModels);
    }
}
