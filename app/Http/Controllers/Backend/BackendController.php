<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Room;

class BackendController extends Controller
{
    /**
     * Show the backend dashboard.
     */
    public function index()
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth();

        $pendingCheckIns = Booking::where('booking_status', 'confirmed')
            ->whereDate('check_in_date', $today)
            ->count();

        $pendingCheckOuts = Booking::where('booking_status', 'checked_in')
            ->whereDate('check_out_date', $today)
            ->count();

        $occupiedRooms = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();

        $dailyIncome = Payment::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $monthlyRevenue = Payment::whereBetween('created_at', [$startOfMonth, now()])
            ->where('status', 'completed')
            ->sum('amount');

        $activeReservations = Booking::whereIn('booking_status', ['pending', 'confirmed', 'checked_in'])
            ->count();

        $alerts = Invoice::where('status', 'pending')->count() + Booking::where('booking_status', 'confirmed')
            ->whereDate('check_in_date', '<', $today)
            ->count();

        $recentBookings = Booking::with(['guest', 'room'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $upcomingReservations = Booking::with(['guest', 'room'])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->whereBetween('check_in_date', [$today, now()->addDays(7)->toDateString()])
            ->orderBy('check_in_date')
            ->limit(7)
            ->get();

        $chartLabels = [];
        $chartRevenue = [];

        for ($days = 6; $days >= 0; $days--) {
            $date = now()->subDays($days);
            $chartLabels[] = $date->format('M d');
            $chartRevenue[] = Payment::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('amount');
        }

        $modules = [
            [
                'title' => 'Bookings',
                'description' => 'Create stays, check guests in, and manage check-outs.',
                'icon' => 'bi-calendar-check',
                'permission' => 'bookings.view',
                'route' => 'backend.bookings.index',
            ],
            [
                'title' => 'Reservations',
                'description' => 'Review reservations and open the booking calendar.',
                'icon' => 'bi-calendar-event',
                'permission' => 'reservations.view',
                'route' => 'backend.reservations.index',
            ],
            [
                'title' => 'Rooms',
                'description' => 'Manage rooms, availability, status, and cleaning.',
                'icon' => 'bi-house-door',
                'permission' => 'rooms.view',
                'route' => 'backend.rooms.index',
            ],
            [
                'title' => 'Guests',
                'description' => 'Find guest profiles, history, and contact details.',
                'icon' => 'bi-people',
                'permission' => 'guests.view',
                'route' => 'backend.guests.index',
            ],
            [
                'title' => 'Invoices',
                'description' => 'Create invoices, review balances, and export PDFs.',
                'icon' => 'bi-receipt',
                'permission' => 'invoices.view',
                'route' => 'backend.invoices.index',
            ],
            [
                'title' => 'Payments',
                'description' => 'Record payments, view summaries, and handle refunds.',
                'icon' => 'bi-cash',
                'permission' => 'payments.view',
                'route' => 'backend.payments.index',
            ],
            [
                'title' => 'Services',
                'description' => 'Maintain add-on services and pricing.',
                'icon' => 'bi-star',
                'permission' => 'services.view',
                'route' => 'backend.services.index',
            ],
            [
                'title' => 'Reports',
                'description' => 'Open occupancy, revenue, booking, and guest reports.',
                'icon' => 'bi-graph-up',
                'permission' => 'reports.view',
                'route' => 'backend.reports.index',
            ],
            [
                'title' => 'Users',
                'description' => 'Manage staff accounts and access.',
                'icon' => 'bi-person-gear',
                'permission' => 'users.view',
                'route' => 'backend.users.index',
            ],
            [
                'title' => 'Settings',
                'description' => 'Adjust hotel, booking, payment, and email settings.',
                'icon' => 'bi-gear',
                'permission' => 'settings.view',
                'route' => 'backend.settings.index',
            ],
        ];

        return view('backend.index', compact(
            'modules',
            'pendingCheckIns',
            'pendingCheckOuts',
            'occupiedRooms',
            'availableRooms',
            'dailyIncome',
            'monthlyRevenue',
            'activeReservations',
            'alerts',
            'recentBookings',
            'upcomingReservations',
            'chartLabels',
            'chartRevenue'
        ));
    }
}
