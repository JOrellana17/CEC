<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function filters(array $input): array
    {
        $from = Carbon::parse($input['date_from'] ?? now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($input['date_to'] ?? now()->endOfMonth())->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'room_type_id' => $input['room_type_id'] ?? null,
            'status' => $input['status'] ?? null,
        ];
    }

    public function context(array $filters): array
    {
        return [
            'filters' => $filters,
            'roomTypes' => RoomType::where('is_active', true)->orderBy('name')->get(),
            'statuses' => ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'],
        ];
    }

    public function operational(array $filters): array
    {
        $rooms = Room::with('roomType')->active()
            ->when($filters['room_type_id'], fn (Builder $query, $type) => $query->where('room_type_id', $type))
            ->get();

        $activeStatuses = ['pending', 'confirmed', 'checked_in'];
        $occupiedStatuses = ['confirmed', 'checked_in', 'checked_out'];
        $start = Carbon::parse($filters['date_from']);
        $end = Carbon::parse($filters['date_to']);
        $days = max(1, $start->diffInDays($end) + 1);

        $overlappingBookings = $this->bookingDateQuery($filters, 'check_in_date', 'check_out_date')
            ->whereIn('booking_status', $occupiedStatuses)
            ->get();

        $bookedNights = $overlappingBookings->sum(function (Booking $booking) use ($start, $end) {
            $checkIn = Carbon::parse($booking->check_in_date)->max($start);
            $checkOut = Carbon::parse($booking->check_out_date)->min($end);

            return max(0, $checkIn->diffInDays($checkOut));
        });

        $capacity = max(1, $rooms->count() * $days);
        $activeReservations = Reservation::with(['guest', 'room.roomType'])
            ->whereIn('status', $activeStatuses)
            ->when($filters['status'], fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['room_type_id'], fn (Builder $query, $type) => $query->whereHas('room', fn ($room) => $room->where('room_type_id', $type)))
            ->where(function (Builder $query) use ($filters) {
                $query->whereBetween('check_in', [$filters['date_from'], $filters['date_to']])
                    ->orWhereBetween('check_out', [$filters['date_from'], $filters['date_to']])
                    ->orWhere(fn ($q) => $q->where('check_in', '<=', $filters['date_from'])->where('check_out', '>=', $filters['date_to']));
            })
            ->orderBy('check_in')
            ->get();

        return [
            'title' => 'Operational Report',
            'occupancy_rate' => round(($bookedNights / $capacity) * 100, 2),
            'total_rooms' => $rooms->count(),
            'available_rooms' => $rooms->where('status', 'available')->values(),
            'active_reservations' => $activeReservations,
            'room_status_summary' => $rooms->groupBy('status')->map->count(),
            'room_type_summary' => $rooms->groupBy(fn ($room) => $room->roomType?->name ?? 'Unassigned')->map->count(),
        ];
    }

    public function financial(array $filters): array
    {
        $payments = Payment::with(['invoice', 'guest'])->completed()
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$filters['date_from'], $filters['date_to']])
            ->when($filters['status'], fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['room_type_id'], fn (Builder $query, $type) => $query->whereHas('invoice.reservation.room', fn ($room) => $room->where('room_type_id', $type)))
            ->get();

        $invoices = Invoice::with('guest')
            ->whereBetween(DB::raw('DATE(issue_date)'), [$filters['date_from'], $filters['date_to']])
            ->when($filters['status'], fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['room_type_id'], fn (Builder $query, $type) => $query->whereHas('reservation.room', fn ($room) => $room->where('room_type_id', $type)))
            ->get();

        return [
            'title' => 'Financial Report',
            'daily_revenue' => $this->dailyRevenue($filters),
            'monthly_revenue' => $this->monthlyRevenue($filters),
            'payment_methods' => $payments->groupBy('payment_method')->map(fn ($rows) => $rows->sum('amount')),
            'outstanding_balances' => $invoices->where('due_amount', '>', 0)->values(),
            'total_revenue' => $payments->sum('amount'),
            'total_invoiced' => $invoices->sum('total_amount'),
            'total_outstanding' => $invoices->sum('due_amount'),
        ];
    }

    public function statistical(array $filters): array
    {
        $guestRows = Guest::withCount(['reservations' => function (Builder $query) use ($filters) {
            $query->whereBetween('check_in', [$filters['date_from'], $filters['date_to']]);
        }])
            ->withSum(['payments as completed_payment_sum' => fn (Builder $query) => $query->where('status', 'completed')], 'amount')
            ->orderByDesc('reservations_count')
            ->take(10)
            ->get();

        $peakSeasons = Reservation::query()
            ->select(DB::raw('MONTH(check_in) as month'), DB::raw('COUNT(*) as total'))
            ->whereBetween('check_in', [$filters['date_from'], $filters['date_to']])
            ->when($filters['status'], fn (Builder $query, $status) => $query->where('status', $status))
            ->when($filters['room_type_id'], fn (Builder $query, $type) => $query->whereHas('room', fn ($room) => $room->where('room_type_id', $type)))
            ->groupBy('month')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['month' => Carbon::create()->month((int) $row->month)->format('F'), 'total' => (int) $row->total]);

        $cancellations = Reservation::query()
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('status', 'cancelled')
            ->whereBetween(DB::raw('DATE(updated_at)'), [$filters['date_from'], $filters['date_to']])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'title' => 'Statistical Report',
            'most_frequent_guests' => $guestRows,
            'peak_seasons' => $peakSeasons,
            'cancellation_trends' => $cancellations,
            'total_cancellations' => $cancellations->sum('total'),
        ];
    }

    public function report(string $type, array $filters): array
    {
        return match ($type) {
            'operational' => $this->operational($filters),
            'financial' => $this->financial($filters),
            'statistical' => $this->statistical($filters),
            default => throw new \InvalidArgumentException('Unsupported report type.'),
        };
    }

    public function rows(string $type, array $report): Collection
    {
        return match ($type) {
            'operational' => collect($report['available_rooms'])->map(fn (Room $room) => [
                'Room' => $room->room_number,
                'Type' => $room->roomType?->name,
                'Status' => $room->status,
                'Cleaning' => $room->room_status,
            ]),
            'financial' => collect($report['outstanding_balances'])->map(fn (Invoice $invoice) => [
                'Invoice' => $invoice->invoice_number,
                'Guest' => $invoice->guest?->full_name,
                'Status' => $invoice->status,
                'Total' => $invoice->total_amount,
                'Paid' => $invoice->paid_amount,
                'Due' => $invoice->due_amount,
            ]),
            'statistical' => collect($report['most_frequent_guests'])->map(fn (Guest $guest) => [
                'Guest' => $guest->full_name,
                'Phone' => $guest->phone,
                'Reservations' => $guest->reservations_count,
                'Completed Payments' => $guest->completed_payment_sum ?? 0,
            ]),
            default => collect(),
        };
    }

    private function bookingDateQuery(array $filters, string $startColumn, string $endColumn): Builder
    {
        return Booking::with(['guest', 'room.roomType'])
            ->when($filters['status'], fn (Builder $query, $status) => $query->where('booking_status', $status))
            ->when($filters['room_type_id'], fn (Builder $query, $type) => $query->whereHas('room', fn ($room) => $room->where('room_type_id', $type)))
            ->where(function (Builder $query) use ($filters, $startColumn, $endColumn) {
                $query->whereBetween($startColumn, [$filters['date_from'], $filters['date_to']])
                    ->orWhereBetween($endColumn, [$filters['date_from'], $filters['date_to']])
                    ->orWhere(fn ($q) => $q->where($startColumn, '<=', $filters['date_from'])->where($endColumn, '>=', $filters['date_to']));
            });
    }

    private function dailyRevenue(array $filters): Collection
    {
        return Payment::completed()
            ->select(DB::raw('DATE(COALESCE(payment_date, created_at)) as date'), DB::raw('SUM(amount) as total'))
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$filters['date_from'], $filters['date_to']])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function monthlyRevenue(array $filters): Collection
    {
        return Payment::completed()
            ->select(DB::raw("DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$filters['date_from'], $filters['date_to']])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
