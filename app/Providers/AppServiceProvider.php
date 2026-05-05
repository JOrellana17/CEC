<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationService;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Booking::class, Guest::class, Invoice::class, Payment::class, Reservation::class, ReservationService::class, Room::class, Service::class, User::class] as $model) {
            $model::observe(AuditObserver::class);
        }

        Gate::before(fn (User $user, string $ability) => $user->hasPermission($ability) ?: null);
    }
}
