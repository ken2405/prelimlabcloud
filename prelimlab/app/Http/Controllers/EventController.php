<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EventResource;
use App\Http\Requests\StoreEventRequest;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * READ: List all events with optional search
     */
    public function index(Request $request)
    {
        $query = Event::withCount('participants');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->get();
        return EventResource::collection($events);
    }

    /**
     * CREATE: Store a new event
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());

        return response()->json([
            'message' => 'Event created successfully',
            'data' => new EventResource($event)
        ], Response::HTTP_CREATED);
    }

    /**
     * READ: Show a single event
     */
    public function show(Event $event)
    {
        $event->loadCount('participants');
        return new EventResource($event);
    }

    /**
     * UPDATE: Update event details
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'event_date'  => 'sometimes|date|after:today',
            'category'    => 'sometimes|string',
            'capacity'    => 'sometimes|integer|min:' . $event->attendees_count
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => new EventResource($event)
        ]);
    }

    /**
     * DELETE: Remove an event
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ], Response::HTTP_NO_CONTENT);
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