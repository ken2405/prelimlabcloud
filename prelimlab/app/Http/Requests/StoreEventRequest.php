<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // allow request
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'event_date' => 'required|date|after_or_equal:today',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'event_date.after_or_equal' => 'Event date must be today or a future date.',
            'capacity.min' => 'Capacity must be at least 1.'
        ];
    }
}