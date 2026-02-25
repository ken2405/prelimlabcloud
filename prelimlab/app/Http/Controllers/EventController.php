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
     * READ: List all events
     */
     public function index()
    {
        return EventResource::collection(Event::all());
    }

    public function show(Event $event)
    {
        return response()->json([
            'data' => new EventResource($event)
        ]);
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
        ], 201);
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
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->validated());

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
