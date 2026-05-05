<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1|max:10',
            'status' => ['required', Rule::in(['pending', 'confirmed'])],
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'check_in.after' => 'Check-in date must be in the future.',
            'check_out.after' => 'Check-out date must be after check-in date.',
            'guests_count.max' => 'Maximum 10 guests allowed.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check room availability
            $room = \App\Models\Room::find($this->room_id);
            if ($room) {
                $isAvailable = \App\Http\Controllers\Backend\ReservationController::checkRoomAvailability(
                    $this->room_id,
                    $this->check_in,
                    $this->check_out
                );

                if (!$isAvailable) {
                    $validator->errors()->add('room_id', 'The selected room is not available for the chosen dates.');
                }

                // Check room capacity
                if ($this->guests_count > $room->capacity) {
                    $validator->errors()->add('guests_count', "The selected room can only accommodate {$room->capacity} guests.");
                }
            }
        });
    }
}