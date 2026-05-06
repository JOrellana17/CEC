<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('guests.edit');
    }

    public function rules(): array
    {
        $guestId = $this->route('guest')?->id;

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'document_id' => 'required|string|max:50|unique:guests,document_id,' . $guestId,
            'nationality' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'incident_notes' => 'nullable|string|max:1000',
            'is_vip' => 'boolean',
            'is_frequent' => 'boolean',
            'is_blacklisted' => 'boolean',
            'is_active' => 'boolean',
            'status' => 'required|in:active,inactive',
        ];
    }
}
