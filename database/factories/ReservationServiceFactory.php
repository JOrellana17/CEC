<?php

namespace Database\Factories;

use App\Models\ReservationService;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationServiceFactory extends Factory
{
    protected $model = ReservationService::class;

    public function definition(): array
    {
        $reservation = Reservation::inRandomOrder()->first() ?? Reservation::factory()->create();
        $service = Service::inRandomOrder()->first() ?? Service::create([
            'name' => 'Default Service',
            'slug' => 'default-service',
            'description' => 'Fallback service',
            'price' => 20.00,
            'unit' => 'each',
            'category' => 'general',
            'is_active' => true,
            'is_available_24h' => false,
        ]);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'reservation_id' => $reservation->id,
            'service_id' => $service->id,
            'quantity' => $quantity,
            'subtotal' => $service->price * $quantity,
        ];
    }
}
