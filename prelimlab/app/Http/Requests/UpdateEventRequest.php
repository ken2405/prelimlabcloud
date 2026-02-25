<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'event_date'  => 'sometimes|date|after:today',
            'category'    => 'sometimes|string',
            'capacity'    => 'sometimes|integer|min:' . ($event->attendees_count ?? 0),
        ];
    }
}