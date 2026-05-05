<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Reservation;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['guest', 'booking', 'reservation.room']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('issue_date', 'desc')->paginate(20);

        return view('backend.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request)
    {
        $bookings = Booking::whereIn('booking_status', ['checked_in', 'checked_out'])
            ->whereDoesntHave('invoice')
            ->with(['guest', 'room'])
            ->get();
        $reservations = Reservation::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereDoesntHave('invoice')
            ->with(['guest', 'room', 'services.service'])
            ->get();

        $selectedBooking = null;
        if ($request->has('booking_id')) {
            $selectedBooking = Booking::with(['guest', 'room', 'bookingServices.service'])->find($request->booking_id);
        }
        $selectedReservation = null;
        if ($request->has('reservation_id')) {
            $selectedReservation = Reservation::with(['guest', 'room', 'services.service'])->find($request->reservation_id);
        }

        return view('backend.invoices.create', compact('bookings', 'reservations', 'selectedBooking', 'selectedReservation'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request, BillingService $billing)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|required_without:reservation_id|exists:bookings,id',
            'reservation_id' => 'nullable|required_without:booking_id|exists:reservations,id',
            'room_charges' => 'required|numeric|min:0',
            'service_charges' => 'nullable|numeric|min:0',
            'food_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        if (! empty($validated['reservation_id'])) {
            $reservation = Reservation::with(['guest', 'room', 'services.service'])->findOrFail($validated['reservation_id']);
            $invoice = $billing->generateInvoiceForReservation($reservation);
            $invoice->update([
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
            ]);
            $billing->refreshInvoiceForReservation($reservation);

            return redirect()->route('backend.invoices.show', $invoice)
                ->with('success', 'Invoice generated successfully.');
        }

        $booking = Booking::with('guest')->find($validated['booking_id']);

        // Calculate totals
        $subtotal = $validated['room_charges'] + 
                    ($validated['service_charges'] ?? 0) + 
                    ($validated['food_charges'] ?? 0) + 
                    ($validated['other_charges'] ?? 0);
        
        $discountAmount = $validated['discount_amount'] ?? 0;
        $subtotalAfterDiscount = $subtotal - $discountAmount;
        $taxAmount = $subtotalAfterDiscount * config('app.tax_rate', 0.1);
        $totalAmount = $subtotalAfterDiscount + $taxAmount;

        $validated['guest_id'] = $booking->guest_id;
        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['due_amount'] = $totalAmount;
        $validated['status'] = 'pending';
        $validated['created_by'] = Auth::id();

        $invoice = Invoice::create($validated);

        return redirect()->route('backend.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['guest', 'booking.room', 'reservation.room', 'reservation.services.service', 'payments', 'createdBy']);
        return view('backend.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        if (!$invoice->canBeCancelled()) {
            return redirect()->route('backend.invoices.index')
                ->with('error', 'Cannot edit invoice in current status.');
        }

        $invoice->load(['booking.guest', 'booking.room', 'reservation.guest', 'reservation.room']);
        return view('backend.invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if (!$invoice->canBeCancelled()) {
            return redirect()->route('backend.invoices.index')
                ->with('error', 'Cannot update invoice in current status.');
        }

        $validated = $request->validate([
            'room_charges' => 'required|numeric|min:0',
            'service_charges' => 'nullable|numeric|min:0',
            'food_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        // Calculate totals
        $subtotal = $validated['room_charges'] + 
                    ($validated['service_charges'] ?? 0) + 
                    ($validated['food_charges'] ?? 0) + 
                    ($validated['other_charges'] ?? 0);
        
        $discountAmount = $validated['discount_amount'] ?? 0;
        $subtotalAfterDiscount = $subtotal - $discountAmount;
        $taxAmount = $subtotalAfterDiscount * config('app.tax_rate', 0.1);
        $totalAmount = $subtotalAfterDiscount + $taxAmount;

        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        $validated['due_amount'] = $totalAmount - $invoice->paid_amount;

        $invoice->update($validated);

        return redirect()->route('backend.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice)
    {
        $invoice->markAsPaid();

        // Update booking payment status
        if ($invoice->booking) {
            $invoice->booking->update([
                'payment_status' => 'paid',
                'paid_amount' => $invoice->total_amount,
                'due_amount' => 0,
            ]);
        }

        return back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Cancel the invoice.
     */
    public function cancel(Invoice $invoice)
    {
        if (!$invoice->canBeCancelled()) {
            return back()->with('error', 'Cannot cancel invoice in current status.');
        }

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Generate invoice PDF.
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['guest', 'booking.room', 'booking.bookingServices.service', 'reservation.room', 'reservation.services.service', 'payments']);

        $pdf = Pdf::loadView('backend.invoices.pdf', compact('invoice'));
        
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function emailInvoice(Invoice $invoice)
    {
        $invoice->load(['guest', 'booking.room', 'reservation.room', 'reservation.services.service', 'payments']);

        if (! $invoice->guest?->email) {
            return back()->with('error', 'Guest does not have an email address.');
        }

        $pdf = Pdf::loadView('backend.invoices.pdf', compact('invoice'));

        Mail::send([], [], function ($message) use ($invoice, $pdf) {
            $message->to($invoice->guest->email, $invoice->guest->full_name)
                ->subject('Invoice '.$invoice->invoice_number)
                ->html('Please find your invoice attached.')
                ->attachData($pdf->output(), 'invoice-'.$invoice->invoice_number.'.pdf', [
                    'mime' => 'application/pdf',
                ]);
        });

        return back()->with('success', 'Invoice emailed successfully.');
    }
}
