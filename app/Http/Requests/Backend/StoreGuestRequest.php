<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('guests.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:guests,email',
            'phone' => 'required|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'document_id' => 'required|string|max:50|unique:guests,document_id',
            'nationality' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'company' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'incident_notes' => 'nullable|string|max:1000',
            'is_vip' => 'boolean',
            'is_frequent' => 'boolean',
            'is_blacklisted' => 'boolean',
            'is_active' => 'boolean',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'document_id.required' => 'Document ID is required.',
            'document_id.unique' => 'This document ID is already registered.',
            'nationality.required' => 'Nationality is required.',
            'status.required' => 'Status is required.',
        ];
    }
}
