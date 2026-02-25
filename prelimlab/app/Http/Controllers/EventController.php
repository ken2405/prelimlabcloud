<?php
namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use App\Http\Requests\StoreEventRequest;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * DISPLAY A LISTING (READ - INDEX)
     * Includes Search and Filtering (Dev 9 & 10)
     */
    public function index(Request $request)
    {
        $query = Event::withCount('participants');

        // Filter by Search Term (Title)
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by Category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by Date
        if ($request->has('date')) {
            $query->whereDate('event_date', $request->date);
        }

        $events = $query->get();
        return EventResource::collection($events);
    }

    /**
     * STORE A NEWLY CREATED RESOURCE (CREATE)
     * Uses StoreEventRequest for Validation (Dev 13)
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
     * DISPLAY THE SPECIFIED RESOURCE (READ - SHOW)
     */
    public function show(Event $event)
    {
        // Load participant count for the resource
        $event->loadCount('participants');
        return new EventResource($event);
    }

    /**
     * UPDATE THE SPECIFIED RESOURCE (UPDATE)
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'event_date'  => 'sometimes|date|after:today',
            'category'    => 'sometimes|string',
            'capacity'    => 'sometimes|integer|min:1',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => new EventResource($event)
        ]);
    }

    /**
     * REMOVE THE SPECIFIED RESOURCE (DELETE)
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ], Response::HTTP_NO_CONTENT);
    }

}