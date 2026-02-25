<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller {

    public function register(Request $request, Event $event)
    {
        if ($event->participants()->count() >= $event->capacity) {
            return response()->json(['error' => 'Event is full'], 422);
        }

        $participant = $event->participants()->create($request->validate([
            'name' => 'required',
            'email' => 'required|email'
        ]));

        return response()->json($participant, 201);
    } 
}