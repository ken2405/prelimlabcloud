<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    /**
     * GET /api/events/{event}/participants
     * List all participants for an event
     */
    public function index(Event $event)
    {
        $participants = $event->participants()->get();

        return response()->json([
            'success' => true,
            'event_id' => $event->id,
            'event_title' => $event->title,
            'total_registered' => count($participants),
            'capacity' => $event->capacity,
            'remaining_slots' => $event->capacity - count($participants),
            'data' => $participants
        ]);
    }

    /**
     * GET /api/events/{event}/participants/{participant}
     * Show a specific participant
     */
    public function show(Event $event, Participant $participant)
    {
        // Verify participant belongs to this event
        if ($participant->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found in this event'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $participant
        ]);
    }

    /**
     * POST /api/events/{event}/register
     * Register a participant for an event
     */
    public function register(Request $request, Event $event)
    {
        // Check if event is full
        if ($event->isEventFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Event is full. No more slots available.',
                'remaining_slots' => 0
            ], 422);
        }

        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        // Check if email already registered for this event
        $existingParticipant = $event->participants()
            ->where('email', $validated['email'])
            ->first();

        if ($existingParticipant) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered for this event'
            ], 422);
        }

        // Create participant
        $participant = $event->participants()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Participant registered successfully',
            'data' => $participant,
            'event' => [
                'title' => $event->title,
                'remaining_slots' => $event->capacity - $event->participants()->count()
            ]
        ], 201);
    }

    /**
     * PUT /api/events/{event}/participants/{participant}
     * Update participant details
     */
    public function update(Request $request, Event $event, Participant $participant)
    {
        // Verify participant belongs to this event
        if ($participant->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found in this event'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
        ]);

        // Check if new email is already registered for this event (if email changed)
        if (isset($validated['email']) && $validated['email'] !== $participant->email) {
            $existingParticipant = $event->participants()
                ->where('email', $validated['email'])
                ->first();

            if ($existingParticipant) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered for this event'
                ], 422);
            }
        }

        $participant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Participant updated successfully',
            'data' => $participant
        ]);
    }

    /**
     * DELETE /api/events/{event}/participants/{participant}
     * Delete (unregister) a participant from an event
     */
    public function destroy(Event $event, Participant $participant)
    {
        // Verify participant belongs to this event
        if ($participant->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found in this event'
            ], 404);
        }

        $participantName = $participant->name;
        $participant->delete();

        return response()->json([
            'success' => true,
            'message' => "Participant '$participantName' unregistered successfully",
            'remaining_slots' => $event->capacity - $event->participants()->count()
        ]);
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
