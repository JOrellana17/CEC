<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\ReservationService;

class BillingService
{
    public function generateInvoiceForReservation(Reservation $reservation): Invoice
    {
        $reservation->loadMissing(['guest', 'room', 'services.service']);

        $invoice = $reservation->invoice ?: new Invoice([
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        $invoice->fill($this->totalsForReservation($reservation, (float) ($invoice->discount_amount ?? 0)));
        $invoice->save();

        return $invoice->fresh();
    }

    public function refreshInvoiceForReservation(Reservation $reservation): ?Invoice
    {
        if (! $reservation->invoice) {
            return null;
        }

        $reservation->loadMissing(['services.service', 'room']);
        $invoice = $reservation->invoice;
        $invoice->fill($this->totalsForReservation($reservation, (float) $invoice->discount_amount));
        $invoice->due_amount = max(0, (float) $invoice->total_amount - (float) $invoice->paid_amount);
        $invoice->status = $invoice->due_amount <= 0 ? 'paid' : ((float) $invoice->paid_amount > 0 ? 'partial' : 'pending');
        $invoice->save();

        return $invoice;
    }

    public function addServiceToReservation(Reservation $reservation, array $data): ReservationService
    {
        $service = \App\Models\Service::findOrFail($data['service_id']);
        $quantity = (int) ($data['quantity'] ?? 1);
        $unitPrice = (float) ($data['unit_price'] ?? $service->price);

        $reservationService = ReservationService::create([
            'reservation_id' => $reservation->id,
            'service_id' => $service->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'service_date' => $data['service_date'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->refreshInvoiceForReservation($reservation);

        return $reservationService;
    }

    public function deleteReservationService(ReservationService $reservationService): void
    {
        $reservation = $reservationService->reservation;
        $reservationService->delete();
        $this->refreshInvoiceForReservation($reservation);
    }

    private function totalsForReservation(Reservation $reservation, float $discountAmount = 0): array
    {
        $nights = max(1, $reservation->check_in->diffInDays($reservation->check_out));
        $roomCharges = (float) $reservation->room->price_per_night * $nights;
        $serviceCharges = (float) $reservation->services->sum(fn ($item) => (float) ($item->total_price ?: $item->subtotal));
        $subtotal = $roomCharges + $serviceCharges;
        $taxable = max(0, $subtotal - $discountAmount);
        $taxAmount = $taxable * (float) config('app.tax_rate', 0.1);
        $totalAmount = $taxable + $taxAmount;

        return [
            'room_charges' => $roomCharges,
            'service_charges' => $serviceCharges,
            'food_charges' => 0,
            'other_charges' => 0,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'due_amount' => max(0, $totalAmount - (float) ($reservation->invoice?->paid_amount ?? 0)),
        ];
    }
}
