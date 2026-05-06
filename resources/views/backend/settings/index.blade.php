@extends('layouts.backend')

@section('title', 'Configuración')

@section('content')
@php
    $paymentMethods = json_decode($settings['payment_methods'] ?? '["cash","card","bank_transfer"]', true) ?: [];
@endphp

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">General</div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.settings.update_general') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Nombre del hotel</label><input class="form-control" name="hotel_name" value="{{ old('hotel_name', $settings['hotel_name'] ?? config('app.name')) }}" required></div>
                        <div class="col-12"><label class="form-label">Eslogan</label><input class="form-control" name="hotel_tagline" value="{{ old('hotel_tagline', $settings['hotel_tagline'] ?? '') }}"></div>
                        <div class="col-12"><label class="form-label">Dirección</label><textarea class="form-control" name="hotel_address">{{ old('hotel_address', $settings['hotel_address'] ?? '') }}</textarea></div>
                        <div class="col-12 col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="hotel_phone" value="{{ old('hotel_phone', $settings['hotel_phone'] ?? '') }}"></div>
                        <div class="col-12 col-md-6"><label class="form-label">Correo electrónico</label><input class="form-control" name="hotel_email" value="{{ old('hotel_email', $settings['hotel_email'] ?? '') }}"></div>
                        <div class="col-12 col-md-6"><label class="form-label">Sitio web</label><input class="form-control" name="hotel_website" value="{{ old('hotel_website', $settings['hotel_website'] ?? '') }}"></div>
                        <div class="col-12 col-md-6"><label class="form-label">RTN</label><input class="form-control" name="hotel_tax_id" value="{{ old('hotel_tax_id', $settings['hotel_tax_id'] ?? '') }}"></div>
                        <div class="col-12 col-md-3"><label class="form-label">Moneda</label><input class="form-control" name="default_currency" value="{{ old('default_currency', $settings['default_currency'] ?? 'USD') }}" required></div>
                        <div class="col-12 col-md-3"><label class="form-label">Zona horaria</label><input class="form-control" name="timezone" value="{{ old('timezone', $settings['timezone'] ?? config('app.timezone')) }}" required></div>
                        <div class="col-12 col-md-3"><label class="form-label">Formato de fecha</label><input class="form-control" name="date_format" value="{{ old('date_format', $settings['date_format'] ?? 'Y-m-d') }}" required></div>
                        <div class="col-12 col-md-3"><label class="form-label">Formato de hora</label><input class="form-control" name="time_format" value="{{ old('time_format', $settings['time_format'] ?? 'H:i') }}" required></div>
                    </div>
                    <button class="btn btn-primary mt-3" data-loading>Guardar general</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Reserva</div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.settings.update_booking') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Check-in</label><input type="time" class="form-control" name="default_check_in_time" value="{{ old('default_check_in_time', $settings['default_check_in_time'] ?? '14:00') }}" required></div>
                        <div class="col-6"><label class="form-label">Check-out</label><input type="time" class="form-control" name="default_check_out_time" value="{{ old('default_check_out_time', $settings['default_check_out_time'] ?? '11:00') }}" required></div>
                        <div class="col-6"><label class="form-label">Días de anticipación</label><input type="number" class="form-control" name="max_advance_booking_days" value="{{ old('max_advance_booking_days', $settings['max_advance_booking_days'] ?? 365) }}" required></div>
                        <div class="col-6"><label class="form-label">Horas para cancelación automática</label><input type="number" class="form-control" name="auto_cancel_unpaid_hours" value="{{ old('auto_cancel_unpaid_hours', $settings['auto_cancel_unpaid_hours'] ?? 24) }}"></div>
                        <div class="col-6"><label class="form-label">Noches mínimas</label><input type="number" class="form-control" name="min_stay_nights" value="{{ old('min_stay_nights', $settings['min_stay_nights'] ?? 1) }}" required></div>
                        <div class="col-6"><label class="form-label">Noches máximas</label><input type="number" class="form-control" name="max_stay_nights" value="{{ old('max_stay_nights', $settings['max_stay_nights'] ?? 30) }}" required></div>
                        <div class="col-6"><label class="form-label">Adultos predeterminados</label><input type="number" class="form-control" name="default_adults_per_room" value="{{ old('default_adults_per_room', $settings['default_adults_per_room'] ?? 2) }}" required></div>
                        <div class="col-6"><label class="form-label">Niños predeterminados</label><input type="number" class="form-control" name="default_children_per_room" value="{{ old('default_children_per_room', $settings['default_children_per_room'] ?? 0) }}" required></div>
                        <div class="col-6"><label class="form-label">Cargo por cama extra</label><input type="number" step="0.01" class="form-control" name="extra_bed_charge" value="{{ old('extra_bed_charge', $settings['extra_bed_charge'] ?? 0) }}"></div>
                        <div class="col-6"><label class="form-label">Cargo por niño extra</label><input type="number" step="0.01" class="form-control" name="extra_child_charge" value="{{ old('extra_child_charge', $settings['extra_child_charge'] ?? 0) }}"></div>
                    </div>
                    <input type="hidden" name="booking_confirmation_required" value="0"><input type="hidden" name="allow_online_check_in" value="0">
                    <button class="btn btn-primary mt-3" data-loading>Guardar reservas</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Pago</div>
            <div class="card-body">
                <form method="POST" action="{{ route('backend.settings.update_payment') }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Tasa de impuesto %</label><input type="number" step="0.01" class="form-control" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate'] ?? 10) }}" required></div>
                        <div class="col-6"><label class="form-label">Días de vencimiento</label><input type="number" class="form-control" name="default_payment_due_days" value="{{ old('default_payment_due_days', $settings['default_payment_due_days'] ?? 7) }}" required></div>
                        <div class="col-6"><label class="form-label">Cargo por servicio %</label><input type="number" step="0.01" class="form-control" name="service_charge_rate" value="{{ old('service_charge_rate', $settings['service_charge_rate'] ?? 0) }}"></div>
                        <div class="col-6"><label class="form-label">Mínimo parcial %</label><input type="number" class="form-control" name="min_partial_payment_percentage" value="{{ old('min_partial_payment_percentage', $settings['min_partial_payment_percentage'] ?? 50) }}"></div>
                        <div class="col-12">
                            <label class="form-label">Métodos de pago</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach(['cash','card','bank_transfer','online','credit'] as $method)
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="payment_methods[]" value="{{ $method }}" id="pay-{{ $method }}" @checked(in_array($method, old('payment_methods', $paymentMethods)))><label class="form-check-label" for="pay-{{ $method }}">{{ Str::headline($method) }}</label></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12"><label class="form-label">Términos de factura</label><textarea class="form-control" name="invoice_terms">{{ old('invoice_terms', $settings['invoice_terms'] ?? '') }}</textarea></div>
                    </div>
                    <input type="hidden" name="tax_included_in_price" value="0"><input type="hidden" name="allow_partial_payment" value="0">
                    <button class="btn btn-primary mt-3" data-loading>Guardar pagos</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Herramientas</div>
            <div class="card-body d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('backend.settings.clear_cache') }}">@csrf<button class="btn btn-outline-secondary" data-loading>Limpiar caché</button></form>
                <a class="btn btn-outline-secondary" href="{{ route('backend.settings.export') }}">Exportar JSON</a>
            </div>
        </div>
    </div>
</div>
@endsection
