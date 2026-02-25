<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * READ: List all events
     */
    public function index()
    {

        $query = Event::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('event_date', $request->input('date'));
        }

        // Optionally order by date
        $query->orderBy('event_date', 'asc');

        // universal
        return response()->json($query->get());

    }

    /**
     * CREATE: Store a new event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1'
        ]);

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    /**
     * READ: Show a single event
     */
    public function show(Event $event)
    {
        return response()->json($event);
    }

    /**
     * UPDATE: Update event details
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:' . $event->attendees_count
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * DELETE: Remove an event
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

    /**
     * CUSTOM ACTION: Add attendee with Race Condition protection
     */
    public function addAttendee(Event $event)
    {
        return DB::transaction(function () use ($event) {
            // "lockForUpdate" prevents two users from reading the same 
            // count at the exact same millisecond.
            $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->first();

            if ($lockedEvent->attendees_count >= $lockedEvent->capacity) {
                return response()->json([
                    'message' => 'Event capacity reached'
                ], 400);
            }

            $lockedEvent->increment('attendees_count');

            return response()->json([
                'message' => 'Attendee added',
                'remaining_slots' => $lockedEvent->capacity - $lockedEvent->attendees_count
            ]);
        });
    }
}
