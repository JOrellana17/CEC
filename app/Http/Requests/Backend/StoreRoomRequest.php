<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('rooms.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'room_number' => 'required|string|max:50|unique:rooms,room_number',
            'floor_id' => 'required|exists:floors,id',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|string|max:100',
            'building' => 'nullable|string|max:100',
            'price_per_night' => 'required|numeric|min:0|decimal:0,2',
            'capacity' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|string',
            'status' => 'required|in:available,occupied,reserved,maintenance,blocked',
            'room_status' => 'required|in:clean,dirty,inspected',
            'is_active' => 'boolean',
            'is_smoking' => 'boolean',
            'has_balcony' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'room_number.required' => 'Room number is required.',
            'room_number.unique' => 'This room number already exists.',
            'floor_id.required' => 'Floor is required.',
            'floor_id.exists' => 'Selected floor is invalid.',
            'room_type_id.required' => 'Room type is required.',
            'room_type_id.exists' => 'Selected room type is invalid.',
            'price_per_night.required' => 'Price per night is required.',
            'price_per_night.numeric' => 'Price must be a valid number.',
            'capacity.required' => 'Capacity is required.',
            'capacity.integer' => 'Capacity must be a whole number.',
            'status.required' => 'Status is required.',
            'room_status.required' => 'Cleaning status is required.',
        ];
    }
}
