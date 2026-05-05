<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'module' => 'dashboard', 'description' => 'View dashboard'],

            // Bookings
            ['name' => 'bookings.view', 'module' => 'bookings', 'description' => 'View bookings list'],
            ['name' => 'bookings.create', 'module' => 'bookings', 'description' => 'Create new bookings'],
            ['name' => 'bookings.edit', 'module' => 'bookings', 'description' => 'Edit bookings'],
            ['name' => 'bookings.delete', 'module' => 'bookings', 'description' => 'Delete bookings'],
            ['name' => 'bookings.cancel', 'module' => 'bookings', 'description' => 'Cancel bookings'],
            ['name' => 'bookings.checkin', 'module' => 'bookings', 'description' => 'Check in guests'],
            ['name' => 'bookings.checkout', 'module' => 'bookings', 'description' => 'Check out guests'],

            // Reservations
            ['name' => 'reservations.view', 'module' => 'reservations', 'description' => 'View reservations list'],
            ['name' => 'reservations.create', 'module' => 'reservations', 'description' => 'Create new reservations'],
            ['name' => 'reservations.edit', 'module' => 'reservations', 'description' => 'Edit reservations'],
            ['name' => 'reservations.delete', 'module' => 'reservations', 'description' => 'Delete reservations'],
            ['name' => 'reservations.confirm', 'module' => 'reservations', 'description' => 'Confirm reservations'],
            ['name' => 'reservations.cancel', 'module' => 'reservations', 'description' => 'Cancel reservations'],
            ['name' => 'reservations.calendar', 'module' => 'reservations', 'description' => 'View reservation calendar'],

            // Rooms
            ['name' => 'rooms.view', 'module' => 'rooms', 'description' => 'View rooms list'],
            ['name' => 'rooms.create', 'module' => 'rooms', 'description' => 'Create new rooms'],
            ['name' => 'rooms.edit', 'module' => 'rooms', 'description' => 'Edit rooms'],
            ['name' => 'rooms.delete', 'module' => 'rooms', 'description' => 'Delete rooms'],
            ['name' => 'rooms.update_status', 'module' => 'rooms', 'description' => 'Update room status'],
            ['name' => 'rooms.update_cleaning_status', 'module' => 'rooms', 'description' => 'Update cleaning status'],

            // Room Types
            ['name' => 'room_types.view', 'module' => 'room_types', 'description' => 'View room types'],
            ['name' => 'room_types.create', 'module' => 'room_types', 'description' => 'Create room types'],
            ['name' => 'room_types.edit', 'module' => 'room_types', 'description' => 'Edit room types'],
            ['name' => 'room_types.delete', 'module' => 'room_types', 'description' => 'Delete room types'],

            // Floors
            ['name' => 'floors.view', 'module' => 'floors', 'description' => 'View floors'],
            ['name' => 'floors.create', 'module' => 'floors', 'description' => 'Create floors'],
            ['name' => 'floors.edit', 'module' => 'floors', 'description' => 'Edit floors'],
            ['name' => 'floors.delete', 'module' => 'floors', 'description' => 'Delete floors'],

            // Guests
            ['name' => 'guests.view', 'module' => 'guests', 'description' => 'View guests list'],
            ['name' => 'guests.create', 'module' => 'guests', 'description' => 'Create new guests'],
            ['name' => 'guests.edit', 'module' => 'guests', 'description' => 'Edit guest information'],
            ['name' => 'guests.delete', 'module' => 'guests', 'description' => 'Delete guests'],

            // Invoices
            ['name' => 'invoices.view', 'module' => 'invoices', 'description' => 'View invoices'],
            ['name' => 'invoices.create', 'module' => 'invoices', 'description' => 'Create invoices'],
            ['name' => 'invoices.edit', 'module' => 'invoices', 'description' => 'Edit invoices'],
            ['name' => 'invoices.delete', 'module' => 'invoices', 'description' => 'Delete invoices'],
            ['name' => 'invoices.cancel', 'module' => 'invoices', 'description' => 'Cancel invoices'],
            ['name' => 'invoices.export', 'module' => 'invoices', 'description' => 'Export invoices'],

            // Payments
            ['name' => 'payments.view', 'module' => 'payments', 'description' => 'View payments'],
            ['name' => 'payments.create', 'module' => 'payments', 'description' => 'Record payments'],
            ['name' => 'payments.refund', 'module' => 'payments', 'description' => 'Refund payments'],

            // Services
            ['name' => 'services.view', 'module' => 'services', 'description' => 'View services'],
            ['name' => 'services.create', 'module' => 'services', 'description' => 'Create services'],
            ['name' => 'services.edit', 'module' => 'services', 'description' => 'Edit services'],
            ['name' => 'services.delete', 'module' => 'services', 'description' => 'Delete services'],

            // Reports
            ['name' => 'reports.view', 'module' => 'reports', 'description' => 'View reports'],
            ['name' => 'reports.export', 'module' => 'reports', 'description' => 'Export reports'],

            // Audit
            ['name' => 'audit.view', 'module' => 'audit', 'description' => 'View audit logs'],

            // Users
            ['name' => 'users.view', 'module' => 'users', 'description' => 'View users'],
            ['name' => 'users.create', 'module' => 'users', 'description' => 'Create users'],
            ['name' => 'users.edit', 'module' => 'users', 'description' => 'Edit users'],
            ['name' => 'users.delete', 'module' => 'users', 'description' => 'Delete users'],

            // Roles
            ['name' => 'roles.view', 'module' => 'roles', 'description' => 'View roles'],
            ['name' => 'roles.create', 'module' => 'roles', 'description' => 'Create roles'],
            ['name' => 'roles.edit', 'module' => 'roles', 'description' => 'Edit roles'],
            ['name' => 'roles.delete', 'module' => 'roles', 'description' => 'Delete roles'],

            // Permissions
            ['name' => 'permissions.view', 'module' => 'permissions', 'description' => 'View permissions'],
            ['name' => 'permissions.create', 'module' => 'permissions', 'description' => 'Create permissions'],
            ['name' => 'permissions.edit', 'module' => 'permissions', 'description' => 'Edit permissions'],
            ['name' => 'permissions.delete', 'module' => 'permissions', 'description' => 'Delete permissions'],

            // Settings
            ['name' => 'settings.view', 'module' => 'settings', 'description' => 'View settings'],
            ['name' => 'settings.update', 'module' => 'settings', 'description' => 'Update settings'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'slug' => Str::slug($permissionData['name']),
                    'module' => $permissionData['module'],
                    'description' => $permissionData['description'],
                    'guard_name' => 'web',
                ]
            );
        }
    }
}
