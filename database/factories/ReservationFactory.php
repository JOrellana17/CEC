<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Room;
use App\Models\Floor;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $guest = Guest::inRandomOrder()->first() ?? Guest::create([
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
        ]);

        $room = Room::inRandomOrder()->first() ?? $this->createFallbackRoom();

        $checkIn = $this->faker->dateTimeBetween('+1 days', '+10 days');
        $checkOut = $this->faker->dateTimeBetween($checkIn->format('Y-m-d').' +1 days', $checkIn->format('Y-m-d').' +7 days');

        return [
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'guests_count' => $this->faker->numberBetween(1, 4),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled']),
            'notes' => $this->faker->sentence(),
        ];
    }

    private function createFallbackRoom(): Room
    {
        $floor = Floor::first() ?? Floor::create(['name' => '1st Floor', 'number' => 1, 'description' => 'Default floor']);
        $roomType = RoomType::first() ?? RoomType::create([
            'name' => 'Standard',
            'slug' => 'standard',
            'description' => 'Standard room',
            'base_price' => 100.00,
            'capacity' => 2,
            'max_capacity' => 2,
            'is_active' => true,
        ]);

        return Room::create([
            'room_number' => '100',
            'floor_id' => $floor->id,
            'room_type_id' => $roomType->id,
            'status' => 'available',
            'room_status' => 'clean',
            'is_active' => true,
        ]);
    }
    }

