<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource (with search, filters, and pagination).
     */
    public function index(Request $request)
    {
        $events = Event::query()
            // Eager load the organizer details (id, name, email only)
            ->with('organizer:id,name,email')
            // 1. Search Function (Title or Description)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            // 2. Filter Functions
            ->when($request->location, function ($query, $location) {
                $query->where('location', $location);
            })
            ->when($request->organizer_id, function ($query, $organizerId) {
                $query->where('organizer_id', $organizerId);
            })
            ->when($request->start_date, function ($query, $startDate) {
                $query->where('start_date', '>=', $startDate);
            })
            ->when($request->end_date, function ($query, $endDate) {
                $query->where('end_date', '<=', $endDate);
            })
            // 3. Pagination (defaults to 20 per page)
            ->paginate($request->input('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming request data
        $validatedData = $request->validate([
            'organizer_id' => 'required|exists:users,id',
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:150',
            'start_date' => 'required|date',
            // Ensure end_date happens after or at the exact same time as start_date
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
        ]);

        // 2. Create the event
        $event = Event::create($validatedData);

        // Load the organizer relationship for the API response
        $event->load('organizer:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the event and eager load its organizer and all registrations
        $event = Event::with(['organizer:id,name,email', 'registrations.user:id,name,email'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        // 1. Validate the incoming request (allowing partial updates)
        $validatedData = $request->validate([
            'organizer_id' => 'sometimes|required|exists:users,id',
            'title' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:150',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
        ]);

        // 2. Update the event
        $event->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);
        
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }
}