<?php
namespace App\Http\Controllers;
use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;

class EventController extends Controller {
    // Dev 8: Public Listing & Dev 9, 10: Search/Filter
    public function index(Request $request) {
        $query = Event::query();
        if ($request->has('search')) $query->where('title', 'like', "%{$request->search}%");
        if ($request->has('category')) $query->where('category', $request->category);
        if ($request->has('date')) $query->whereDate('event_date', $request->date);

        return EventResource::collection($query->get());
    }

    public function store(StoreEventRequest $request) {
        $event = Event::create($request->validated());
        return new EventResource($event);
    }

    // Dev 11: Event Statistics
    public function stats() {
        return response()->json([
            'total_events' => Event::count(),
            'total_capacity' => Event::sum('capacity'),
            'top_category' => Event::select('category')->groupBy('category')->orderByRaw('COUNT(*) DESC')->first(),
        ]);
    }
}