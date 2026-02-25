<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class PublicEventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::withCount('participants')
            ->where('is_public', true)
            ->where('is_approved', true);

        // Optional search/filter
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->date);
        }

        // upcoming events
        $query->where('event_date', '>=', now())
              ->orderBy('event_date', 'asc');

        $events = $query->paginate(6);

        return view('public.events.index', compact('events'));
    }

    public function show($id)
    {
        $event = Event::withCount('participants')
            ->where('is_public', true)
            ->where('is_approved', true)
            ->findOrFail($id);

        return view('public.events.show', compact('event'));
    }
}