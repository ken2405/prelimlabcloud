<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;   // Make sure Event model exists
use App\Models\Participant; // Optional if you want

class PublicEventController extends Controller
{
    // List all public events
    public function index()
    {
        // Get events that are public, approved, upcoming
        $events = Event::withCount('participants')   // counts participants for capacity
            ->where('is_public', true)
            ->where('is_approved', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->paginate(6);                          // 6 per page

        return view('public.events.index', compact('events'));
    }

    // Optional: Show single event details
    public function show($id)
    {
        $event = Event::withCount('participants')
            ->where('is_public', true)
            ->where('is_approved', true)
            ->findOrFail($id);

        return view('public.events.show', compact('event'));
    }
}