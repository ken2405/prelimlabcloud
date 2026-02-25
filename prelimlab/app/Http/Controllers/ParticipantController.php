<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    /**
     * Register a participant for a specific event.
     * - Checks if the event is already full
     * - Validates input (name, email)
     * - Creates the participant linked to the event
     */
    public function register(Request $request, Event $event)
    {
        // 1) Check capacity first
        $isEventFull = $event->participants()->count() >= $event->capacity;

        if ($isEventFull) {
            return response()->json([
                'error' => 'Event is full'
            ], 422);
        }

        // 2) Validate incoming data
        $validated = $request->validate([
            'name'  => ['required'],
            'email' => ['required', 'email'],
        ]);

        // 3) Create participant under this event
        $participant = $event->participants()->create($validated);

        // 4) Return created participant
        return response()->json($participant, 201);
    }

    public function stats()
    {
        return response()->json([
            'total_events' => Event::count(),
            'total_participants' => Participant::count(),
            'popular_category' => Event::select('category')
                ->groupBy('category')
                ->orderByRaw('COUNT(*) DESC')
                ->value('category'),
        ]);
    }
}