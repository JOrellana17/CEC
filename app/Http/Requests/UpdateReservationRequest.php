<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
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
        $reservation = $this->route('reservation');

        return [
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests_count' => 'required|integer|min:1|max:50',
            'status' => ['required', Rule::in(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])],
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'check_out.after' => 'Check-out date must be after check-in date.',
            'guests_count.max' => 'Maximum 50 guests allowed.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $reservation = $this->route('reservation');

            // Only check availability if dates or room changed
            if ($this->room_id != $reservation->room_id ||
                $this->check_in != $reservation->check_in->format('Y-m-d') ||
                $this->check_out != $reservation->check_out->format('Y-m-d')) {

                $room = \App\Models\Room::find($this->room_id);
                if ($room) {
                    $isAvailable = \App\Http\Controllers\Backend\ReservationController::checkRoomAvailability(
                        $this->room_id,
                        $this->check_in,
                        $this->check_out,
                        $reservation->id
                    );

                    if (!$isAvailable) {
                        $validator->errors()->add('room_id', 'The selected room is not available for the chosen dates.');
                    }

                    // Check maximum lodging capacity. Guests above included capacity can be charged separately.
                    $maxCapacity = $room->max_capacity ?? $room->capacity;
                    if ($this->guests_count > $maxCapacity) {
                        $validator->errors()->add('guests_count', "The selected lodging can only accommodate {$maxCapacity} guests.");
                    }
                }
            }
        });
    }
}
