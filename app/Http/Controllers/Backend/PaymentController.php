<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['guest', 'booking', 'invoice']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $invoices = Invoice::whereIn('status', ['pending', 'partial'])
            ->with(['guest', 'booking'])
            ->get();

        $selectedInvoice = null;
        if ($request->has('invoice_id')) {
            $selectedInvoice = Invoice::with(['guest', 'booking', 'payments'])->find($request->invoice_id);
        }

        return view('backend.payments.create', compact('invoices', 'selectedInvoice'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,online,credit,mixed',
            'transaction_id' => 'nullable|string|max:255',
            'card_last_four' => 'nullable|string|max:4',
            'card_type' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::with(['booking', 'reservation'])->find($validated['invoice_id']);

        // Validate payment amount
        if ($validated['amount'] > $invoice->due_amount) {
            return back()->with('error', 'Payment amount exceeds due amount.')->withInput();
        }

        $validated['booking_id'] = $invoice->booking_id;
        $validated['guest_id'] = $invoice->guest_id;
        $validated['status'] = 'completed';
        $validated['payment_date'] = now();
        $validated['created_by'] = Auth::id();

        $payment = Payment::create($validated);

        // Update invoice
        $invoice->paid_amount = (float) $invoice->paid_amount + (float) $validated['amount'];
        $invoice->due_amount = max(0, (float) $invoice->total_amount - (float) $invoice->paid_amount);
        
        if ($invoice->due_amount <= 0) {
            $invoice->status = 'paid';
            $invoice->paid_date = now();
        } else {
            $invoice->status = 'partial';
        }
        
        $invoice->save();

        // Update booking
        if ($invoice->booking) {
            $invoice->booking->paid_amount = (float) $invoice->booking->paid_amount + (float) $validated['amount'];
            $invoice->booking->due_amount = max(0, (float) $invoice->booking->total_amount - (float) $invoice->booking->paid_amount);
            
            if ($invoice->booking->due_amount <= 0) {
                $invoice->booking->payment_status = 'paid';
            } elseif ($invoice->booking->paid_amount > 0) {
                $invoice->booking->payment_status = 'partial';
            }
            
            $invoice->booking->save();
        }

        return redirect()->route('backend.payments.show', $payment->id)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['guest', 'booking', 'invoice', 'createdBy']);
        return view('backend.payments.show', compact('payment'));
    }

    /**
     * Refund the payment.
     */
    public function refund(Request $request, Payment $payment)
    {
        if (!$payment->canBeRefunded()) {
            return back()->with('error', 'Payment cannot be refunded.');
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        // Update payment status
        $payment->update([
            'status' => 'refunded',
            'notes' => $payment->notes . "\nRefund reason: " . $validated['reason'],
        ]);

        // Update invoice
        $invoice = $payment->invoice;
        $invoice->paid_amount -= $payment->amount;
        $invoice->due_amount += $payment->amount;
        
        if ($invoice->paid_amount <= 0) {
            $invoice->status = 'pending';
            $invoice->paid_date = null;
        } else {
            $invoice->status = 'partial';
        }
        
        $invoice->save();

        // Update booking
        $booking = $invoice->booking;
        if ($booking) {
            $booking->paid_amount -= $payment->amount;
            $booking->due_amount += $payment->amount;
            
            if ($booking->paid_amount <= 0) {
                $booking->payment_status = 'unpaid';
            } elseif ($booking->paid_amount < $booking->total_amount) {
                $booking->payment_status = 'partial';
            }
            
            $booking->save();
        }

        return back()->with('success', 'Payment refunded successfully.');
    }

    /**
     * Get payment methods summary.
     */
    public function summary(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->endOfMonth()->format('Y-m-d'));

        $payments = Payment::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $summary = [
            'total' => $payments->sum('amount'),
            'cash' => $payments->where('payment_method', 'cash')->sum('amount'),
            'card' => $payments->where('payment_method', 'card')->sum('amount'),
            'bank_transfer' => $payments->where('payment_method', 'bank_transfer')->sum('amount'),
            'online' => $payments->where('payment_method', 'online')->sum('amount'),
            'credit' => $payments->where('payment_method', 'credit')->sum('amount'),
            'mixed' => $payments->where('payment_method', 'mixed')->sum('amount'),
            'count' => $payments->count(),
        ];

        return view('backend.payments.summary', compact('summary', 'dateFrom', 'dateTo'));
    }
}
