<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\BackendController;
use App\Http\Controllers\Backend\FloorController;
use App\Http\Controllers\Backend\RoomTypeController;
use App\Http\Controllers\Backend\RoomController;
use App\Http\Controllers\Backend\GuestController;
use App\Http\Controllers\Backend\BookingController;
use App\Http\Controllers\Backend\InvoiceController;
use App\Http\Controllers\Backend\PaymentController;
use App\Http\Controllers\Backend\ServiceController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Backend\ICalendarController;
use App\Http\Controllers\Backend\ReservationServiceController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\AuditLogController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('backend.dashboard')
        : redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'sanitize.input', 'backend.authorize'])->prefix('backend')->name('backend.')->group(function () {

    // Dashboard
    Route::get('/', [BackendController::class, 'index'])->name('dashboard');

    // Floors
    Route::resource('floors', FloorController::class);

    // Room Types
    Route::resource('room-types', RoomTypeController::class);

    // Rooms
    Route::resource('rooms', RoomController::class);
    Route::patch('rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.update_status');
    Route::patch('rooms/{room}/cleaning-status', [RoomController::class, 'updateCleaningStatus'])->name('rooms.update_cleaning_status');
    Route::post('rooms/{id}/restore', [RoomController::class, 'restore'])->name('rooms.restore');
    Route::post('rooms/{room}/check-availability', [RoomController::class, 'checkAvailability'])->name('rooms.check_availability');

    // Guests
    Route::resource('guests', GuestController::class);
    Route::get('guests/search/autocomplete', [GuestController::class, 'search'])->name('guests.search');
    Route::patch('guests/{guest}/toggle-status', [GuestController::class, 'toggleStatus'])->name('guests.toggle_status');
    Route::patch('guests/{guest}/toggle-blacklist', [GuestController::class, 'toggleBlacklist'])->name('guests.toggle_blacklist');
    Route::patch('guests/{guest}/toggle-frequent', [GuestController::class, 'toggleFrequent'])->name('guests.toggle_frequent');
    Route::post('guests/{id}/restore', [GuestController::class, 'restore'])->name('guests.restore');

    // Bookings
    Route::resource('bookings', BookingController::class)->except(['destroy']);
    Route::patch('bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check_in');
    Route::patch('bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check_out');
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('bookings/available-rooms', [BookingController::class, 'availableRooms'])->name('bookings.available_rooms');
    Route::get('bookings/room-price', [BookingController::class, 'getRoomPrice'])->name('bookings.room_price');

    // Reservations
    Route::get('reservations/export/ics', [ICalendarController::class, 'exportRange'])->name('reservations.export_ics_range');
    Route::get('reservations/calendar/export/ics', [ICalendarController::class, 'exportCalendar'])->name('reservations.export_ics_calendar');
    Route::get('reservations/calendar', [\App\Http\Controllers\Backend\ReservationController::class, 'calendar'])->name('reservations.calendar');
    Route::get('reservations/{reservation}/export/ics', [ICalendarController::class, 'exportReservation'])->name('reservations.export_ics');
    Route::resource('reservations', \App\Http\Controllers\Backend\ReservationController::class);
    Route::patch('reservations/{reservation}/confirm', [\App\Http\Controllers\Backend\ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::patch('reservations/{reservation}/cancel', [\App\Http\Controllers\Backend\ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('reservations/check-availability', [\App\Http\Controllers\Backend\ReservationController::class, 'checkAvailability'])->name('reservations.check_availability');
    Route::get('reservations/calendar/events', [\App\Http\Controllers\Backend\ReservationController::class, 'calendarEvents'])->name('reservations.calendar_events');
    Route::patch('reservations/{reservation}/calendar-update', [\App\Http\Controllers\Backend\ReservationController::class, 'updateFromCalendar'])->name('reservations.update_from_calendar');
    Route::post('reservations/{reservation}/services', [ReservationServiceController::class, 'store'])->name('reservations.services.store');
    Route::delete('reservation-services/{reservationService}', [ReservationServiceController::class, 'destroy'])->name('reservations.services.destroy');

    // iCalendar integration
    Route::get('icalendar', [ICalendarController::class, 'index'])->name('icalendar.index');
    Route::patch('icalendar/settings', [ICalendarController::class, 'updateSettings'])->name('icalendar.settings');
    Route::post('icalendar/import/preview', [ICalendarController::class, 'previewImport'])->name('icalendar.import_preview');
    Route::post('icalendar/import/confirm', [ICalendarController::class, 'confirmImport'])->name('icalendar.import_confirm');
    Route::post('icalendar/sync', [ICalendarController::class, 'syncNow'])->name('icalendar.sync');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark_paid');
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/email', [InvoiceController::class, 'emailInvoice'])->name('invoices.email');

    // Payments
    Route::get('payments/summary', [PaymentController::class, 'summary'])->name('payments.summary');
    Route::resource('payments', PaymentController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

    // Services
    Route::get('services/by-category', [ServiceController::class, 'getByCategory'])->name('services.by_category');
    Route::resource('services', ServiceController::class);
    Route::patch('services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle_status');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/operational', [ReportController::class, 'show'])->defaults('type', 'operational')->name('reports.operational');
    Route::get('reports/financial', [ReportController::class, 'show'])->defaults('type', 'financial')->name('reports.financial');
    Route::get('reports/statistical', [ReportController::class, 'show'])->defaults('type', 'statistical')->name('reports.statistical');
    Route::get('reports/{type}/pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
    Route::get('reports/{type}/excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');

    // Audit logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.update_general');
    Route::patch('settings/booking', [SettingsController::class, 'updateBooking'])->name('settings.update_booking');
    Route::patch('settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.update_payment');
    Route::patch('settings/notification', [SettingsController::class, 'updateNotification'])->name('settings.update_notification');
    Route::patch('settings/email', [SettingsController::class, 'updateEmail'])->name('settings.update_email');
    Route::post('settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear_cache');
    Route::get('settings/export', [SettingsController::class, 'export'])->name('settings.export');
    Route::post('settings/import', [SettingsController::class, 'import'])->name('settings.import');

    // Users
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle_status');
    Route::patch('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.update_password');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset_password');
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign_role');
    Route::delete('users/{user}/revoke-role/{role}', [UserController::class, 'revokeRole'])->name('users.revoke_role');
    Route::post('users/{user}/give-permission', [UserController::class, 'givePermission'])->name('users.give_permission');
    Route::delete('users/{user}/revoke-permission/{permission}', [UserController::class, 'revokePermission'])->name('users.revoke_permission');
    Route::get('profile', [UserController::class, 'profile'])->name('users.profile');
    Route::patch('profile', [UserController::class, 'updateProfile'])->name('users.update_profile');
    Route::post('profile/avatar', [UserController::class, 'updateAvatar'])->name('users.update_avatar');

    // Roles
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/give-permission', [RoleController::class, 'givePermission'])->name('roles.give_permission');
    Route::delete('roles/{role}/revoke-permission/{permission}', [RoleController::class, 'revokePermission'])->name('roles.revoke_permission');

    // Permissions
    Route::resource('permissions', PermissionController::class);
    Route::get('permissions/by-module', [PermissionController::class, 'getByModule'])->name('permissions.by_module');

});
