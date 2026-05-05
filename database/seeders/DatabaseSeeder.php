<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Booking;
use App\Models\Floor;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@hotel.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }

        // Create supervisor user
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@hotel.com'],
            [
                'name' => 'Supervisor User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $supervisorRole = Role::where('name', 'supervisor')->first();
        if ($supervisorRole && !$supervisor->hasRole($supervisorRole)) {
            $supervisor->assignRole($supervisorRole);
        }

        // Create test user
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $this->seedHotelData($admin);
    }

    private function seedHotelData(User $admin): void
    {
        $floor = Floor::firstOrCreate(['number' => 1], ['name' => 'First Floor', 'description' => 'Main guest floor', 'is_active' => true]);

        $standard = RoomType::firstOrCreate(['slug' => 'standard-queen'], [
            'name' => 'Standard Queen',
            'description' => 'Comfortable queen room for short stays.',
            'base_price' => 95,
            'capacity' => 2,
            'max_capacity' => 3,
            'bed_type' => 'Queen',
            'amenities' => ['WiFi', 'Desk', 'TV'],
            'is_active' => true,
        ]);

        $suite = RoomType::firstOrCreate(['slug' => 'executive-suite'], [
            'name' => 'Executive Suite',
            'description' => 'Larger suite with lounge workspace.',
            'base_price' => 180,
            'capacity' => 2,
            'max_capacity' => 4,
            'bed_type' => 'King',
            'amenities' => ['WiFi', 'Mini bar', 'Workspace'],
            'is_active' => true,
        ]);

        $rooms = collect([
            ['101', $standard, 'available', 95],
            ['102', $standard, 'occupied', 95],
            ['103', $standard, 'reserved', 105],
            ['201', $suite, 'available', 180],
        ])->map(fn ($row) => Room::firstOrCreate(['room_number' => $row[0]], [
            'floor_id' => $floor->id,
            'room_type_id' => $row[1]->id,
            'floor' => '1',
            'building' => 'Main',
            'price_per_night' => $row[3],
            'capacity' => $row[1]->capacity,
            'status' => $row[2],
            'room_status' => 'clean',
            'is_active' => true,
        ]));

        $guest = Guest::firstOrCreate(['email' => 'maria.garcia@example.com'], [
            'first_name' => 'Maria',
            'last_name' => 'Garcia',
            'full_name' => 'Maria Garcia',
            'phone' => '+504 5555-0101',
            'document_id' => 'HN-0001',
            'nationality' => 'Honduran',
            'status' => 'active',
            'is_active' => true,
            'is_frequent' => true,
        ]);

        $booking = Booking::firstOrCreate(['booking_number' => 'BK-SEED-0001'], [
            'guest_id' => $guest->id,
            'room_id' => $rooms[1]->id,
            'check_in_date' => now()->subDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'adults' => 2,
            'room_rate' => 95,
            'subtotal' => 285,
            'tax_amount' => 42.75,
            'total_amount' => 327.75,
            'paid_amount' => 200,
            'due_amount' => 127.75,
            'booking_status' => 'checked_in',
            'payment_status' => 'partial',
            'payment_method' => 'card',
            'created_by' => $admin->id,
        ]);

        Reservation::firstOrCreate([
            'guest_id' => $guest->id,
            'room_id' => $rooms[2]->id,
            'check_in' => now()->addDays(8)->toDateString(),
        ], [
            'check_out' => now()->addDays(11)->toDateString(),
            'guests_count' => 2,
            'status' => 'confirmed',
            'notes' => 'Seeder reservation for reporting.',
        ]);

        $invoice = Invoice::firstOrCreate(['invoice_number' => 'INV-SEED-0001'], [
            'booking_id' => $booking->id,
            'guest_id' => $guest->id,
            'room_charges' => 285,
            'subtotal' => 285,
            'tax_amount' => 42.75,
            'total_amount' => 327.75,
            'paid_amount' => 200,
            'due_amount' => 127.75,
            'status' => 'partial',
            'issue_date' => now()->subDay(),
            'due_date' => now()->addDays(2),
            'created_by' => $admin->id,
        ]);

        Payment::firstOrCreate(['payment_number' => 'PAY-SEED-0001'], [
            'invoice_id' => $invoice->id,
            'booking_id' => $booking->id,
            'guest_id' => $guest->id,
            'amount' => 200,
            'payment_method' => 'card',
            'reference_number' => Str::upper(Str::random(8)),
            'status' => 'completed',
            'payment_date' => now()->subDay(),
            'created_by' => $admin->id,
        ]);
    }
}
