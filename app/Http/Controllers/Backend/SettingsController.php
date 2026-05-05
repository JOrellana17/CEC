<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('backend.settings.index', compact('settings'));
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'hotel_name' => 'required|string|max:255',
            'hotel_tagline' => 'nullable|string|max:255',
            'hotel_address' => 'nullable|string',
            'hotel_phone' => 'nullable|string|max:50',
            'hotel_email' => 'nullable|email|max:255',
            'hotel_website' => 'nullable|url|max:255',
            'hotel_tax_id' => 'nullable|string|max:100',
            'default_currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        // Clear cache
        Cache::flush();

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Update booking settings.
     */
    public function updateBooking(Request $request)
    {
        $validated = $request->validate([
            'default_check_in_time' => 'required|date_format:H:i',
            'default_check_out_time' => 'required|date_format:H:i',
            'max_advance_booking_days' => 'required|integer|min:1',
            'min_stay_nights' => 'required|integer|min:1',
            'max_stay_nights' => 'required|integer|min:1',
            'booking_confirmation_required' => 'boolean',
            'allow_online_check_in' => 'boolean',
            'auto_cancel_unpaid_hours' => 'nullable|integer|min:1',
            'default_adults_per_room' => 'required|integer|min:1',
            'default_children_per_room' => 'required|integer|min:0',
            'extra_bed_charge' => 'nullable|numeric|min:0',
            'extra_child_charge' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::flush();

        return back()->with('success', 'Booking settings updated successfully.');
    }

    /**
     * Update payment settings.
     */
    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_included_in_price' => 'boolean',
            'service_charge_rate' => 'nullable|numeric|min:0|max:100',
            'payment_methods' => 'required|array',
            'payment_methods.*' => 'in:cash,card,bank_transfer,online,credit',
            'allow_partial_payment' => 'boolean',
            'min_partial_payment_percentage' => 'nullable|integer|min:1|max:100',
            'invoice_terms' => 'nullable|string',
            'default_payment_due_days' => 'required|integer|min:1',
        ]);

        $validated['payment_methods'] = json_encode($validated['payment_methods']);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::flush();

        return back()->with('success', 'Payment settings updated successfully.');
    }

    /**
     * Update notification settings.
     */
    public function updateNotification(Request $request)
    {
        $validated = $request->validate([
            'email_notifications_enabled' => 'boolean',
            'sms_notifications_enabled' => 'boolean',
            'booking_confirmation_template' => 'nullable|string',
            'booking_confirmation_subject' => 'nullable|string|max:255',
            'check_in_reminder_hours' => 'nullable|integer|min:1',
            'check_out_reminder_hours' => 'nullable|integer|min:1',
            'payment_receipt_template' => 'nullable|string',
            'payment_receipt_subject' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::flush();

        return back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Update email settings.
     */
    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'mail_driver' => 'required|in:smtp,mail,sendmail,log',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::flush();

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        Cache::flush();
        
        return back()->with('success', 'Application cache cleared successfully.');
    }

    /**
     * Export settings.
     */
    public function export()
    {
        $settings = Setting::all();
        
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting->key] = $setting->value;
        }

        return response()->json($data);
    }

    /**
     * Import settings.
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'settings_json' => 'required|json',
        ]);

        $settings = json_decode($validated['settings_json'], true);

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::flush();

        return back()->with('success', 'Settings imported successfully.');
    }
}