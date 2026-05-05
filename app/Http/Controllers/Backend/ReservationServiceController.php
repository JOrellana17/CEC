<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationService;
use App\Services\BillingService;
use Illuminate\Http\Request;

class ReservationServiceController extends Controller
{
    public function store(Request $request, Reservation $reservation, BillingService $billing)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'service_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $billing->addServiceToReservation($reservation, $validated);

        return back()->with('success', 'Service added and invoice totals updated.');
    }

    public function destroy(ReservationService $reservationService, BillingService $billing)
    {
        $billing->deleteReservationService($reservationService);

        return back()->with('success', 'Service removed and invoice totals updated.');
    }
}
