<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'capacity' => 'required|integer|min:1'
        ]);

        $event = Event::create($request->only('title', 'capacity'));

        return response()->json($event);
    }

    public function addAttendee(Event $event)
    {
        return DB::transaction(function () use ($event) {

            if ($event->attendees_count >= $event->capacity) {
                return response()->json([
                    'message' => 'Event capacity reached'
                ], 400);
            }

            $event->increment('attendees_count');

            return response()->json([
                'message' => 'Attendee added',
                'remaining_slots' => $event->capacity - $event->attendees_count
            ]);
        });
    }
}