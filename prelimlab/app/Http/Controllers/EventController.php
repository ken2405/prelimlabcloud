<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // 1. READ ALL (Includes search/filter logic from Devs 9 & 10)
    public function index(Request $request) 
    {
        
    }

    // 2. CREATE
    public function store(Request $request) 
    {
        // Validating directly via the standard Request object
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date|after:today',
            'category' => 'required|string',
            'capacity' => 'required|integer|min:1',
        ]);

        $event = Event::create($validated);
        
        return new EventResource($event);
    }

    // 3. READ SINGLE
    public function show(Event $event) 
    {
        // Laravel automatically fetches the event by ID via Route Model Binding
        return new EventResource($event);
    }

    // 4. UPDATE
    public function update(Request $request, Event $event) 
    {
        // Using 'sometimes' means it only validates if the field is present in the request
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'event_date' => 'sometimes|date|after:today',
            'category' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1',
        ]);

        $event->update($validated);
        
        return new EventResource($event);
    }

    // 5. DELETE
    public function destroy(Event $event) 
    {
        $event->delete();
        
        return response()->json([
            'message' => 'Event deleted successfully'
        ], 200); // 200 OK status
    }
}