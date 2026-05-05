<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $reservation = Reservation::inRandomOrder()->first() ?? Reservation::factory()->create();
        $start = $this->faker->dateTimeBetween('+1 days', '+14 days');
        $end = (clone $start)->modify('+'.rand(1, 5).' days');

        return [
            'reservation_id' => $reservation->id,
            'event_uid' => $this->faker->uuid(),
            'start_date' => $start,
            'end_date' => $end,
        ];
    }
}
