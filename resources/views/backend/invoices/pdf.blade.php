<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        .text-right { text-align: right; }
        .muted { color: #666; }
        .total { font-weight: bold; background: #f3f3f3; }
    </style>
</head>
<body>
    <h1>{{ $invoice->invoice_number }}</h1>
    <p class="muted">Issue Date: {{ $invoice->issue_date?->format('Y-m-d') }}</p>

    <p>
        <strong>Guest:</strong> {{ $invoice->guest?->full_name ?? 'No guest' }}<br>
        <strong>Booking:</strong> {{ $invoice->booking?->booking_number ?? 'No booking' }}<br>
        <strong>Status:</strong> {{ ucfirst($invoice->status) }}
    </p>

    <table>
        <tbody>
            <tr><th>Room Charges</th><td class="text-right">${{ number_format((float) $invoice->room_charges, 2) }}</td></tr>
            <tr><th>Service Charges</th><td class="text-right">${{ number_format((float) $invoice->service_charges, 2) }}</td></tr>
            <tr><th>Food Charges</th><td class="text-right">${{ number_format((float) $invoice->food_charges, 2) }}</td></tr>
            <tr><th>Other Charges</th><td class="text-right">${{ number_format((float) $invoice->other_charges, 2) }}</td></tr>
            <tr><th>Subtotal</th><td class="text-right">${{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
            <tr><th>Discount</th><td class="text-right">${{ number_format((float) $invoice->discount_amount, 2) }}</td></tr>
            <tr><th>Tax</th><td class="text-right">${{ number_format((float) $invoice->tax_amount, 2) }}</td></tr>
            <tr class="total"><th>Total</th><td class="text-right">${{ number_format((float) $invoice->total_amount, 2) }}</td></tr>
            <tr><th>Paid</th><td class="text-right">${{ number_format((float) $invoice->paid_amount, 2) }}</td></tr>
            <tr><th>Due</th><td class="text-right">${{ number_format((float) $invoice->due_amount, 2) }}</td></tr>
        </tbody>
    </table>

    @if($invoice->notes)
    <h3>Notes</h3>
    <p>{{ $invoice->notes }}</p>
    @endif

    @if($invoice->terms)
    <h3>Terms</h3>
    <p>{{ $invoice->terms }}</p>
    @endif
</body>
</html>
