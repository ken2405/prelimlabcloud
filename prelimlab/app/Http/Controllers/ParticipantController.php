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

    /**
     * GET /api/events/{event}/stats
     * Get event participant statistics
     */
    public function stats(Event $event)
    {
        $participantCount = $event->participants()->count();

        return response()->json([
            'success' => true,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'capacity' => $event->capacity,
            ],
            'stats' => [
                'total_registered' => $participantCount,
                'remaining_slots' => $event->capacity - $participantCount,
                'capacity_percentage' => round(($participantCount / $event->capacity) * 100, 2),
                'is_full' => $event->isEventFull(),
            ]
        ]);
    }
}
