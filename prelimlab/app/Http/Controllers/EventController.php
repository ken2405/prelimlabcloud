<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * GET /api/events
     * Public listing with search/filter
     *
     * Query Parameters:
     * - search: search by title or description
     * - category: filter by category
     * - date: filter by exact date (YYYY-MM-DD)
     * - date_from: filter events from date onwards (YYYY-MM-DD)
     * - date_to: filter events until date (YYYY-MM-DD)
     * - sort: sort by field (title, event_date, created_at, capacity) - default: event_date
     * - order: asc or desc - default: asc
     * - limit: results per page - default: 50
     * - page: page number - default: 1
     */
    public function index(Request $request)
    {
        $query = Event::withCount('participants');

        // Search by title or description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by exact date
        if ($request->has('date') && $request->date) {
            $query->whereDate('event_date', $request->date);
        }

        // Filter by date range (from date)
        if ($request->has('date_from') && $request->date_from) {
            $query->where('event_date', '>=', $request->date_from . ' 00:00:00');
        }

        // Filter by date range (to date)
        if ($request->has('date_to') && $request->date_to) {
            $query->where('event_date', '<=', $request->date_to . ' 23:59:59');
        }

        // Sorting
        $sortBy = $request->input('sort', 'event_date');
        $order = $request->input('order', 'asc');

        // Validate sort field to prevent SQL injection
        $allowedSorts = ['title', 'event_date', 'created_at', 'capacity'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'event_date';
        }

        // Validate order
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'asc';
        }

        $query->orderBy($sortBy, $order);

        // Pagination
        $limit = min($request->input('limit', 50), 100); // Max 100 per page
        $results = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->per_page(),
                'current_page' => $results->current_page(),
                'last_page' => $results->last_page(),
            ],
            'data' => $results->items()
        ]);
    }

    /**
     * POST /api/events
     * Create a new event (Authenticated)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date_format:Y-m-d H:i:s|after:now',
            'category' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
        ]);

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    /**
     * GET /api/events/{event}
     * Show single event details
     */
    public function show(Event $event)
    {
        $event->loadCount('participants');

        return response()->json([
            'success' => true,
            'data' => $event,
            'remaining_capacity' => $event->capacity - $event->participants_count,
            'is_full' => $event->isEventFull()
        ]);
    }

    /**
     * PUT /api/events/{event}
     * Update an event (Authenticated)
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'event_date' => 'sometimes|date_format:Y-m-d H:i:s',
            'category' => 'sometimes|string|max:100',
            'capacity' => 'sometimes|integer|min:1',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event
        ]);
    }

    /**
     * DELETE /api/events/{event}
     * Delete an event (Authenticated)
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }

    /**
     * GET /api/stats
     * Get overall system statistics
     */
    public function globalStats()
    {
        $totalEvents = Event::count();
        $totalParticipants = Event::sum('participants') ?? 0;
        $totalCapacity = Event::sum('capacity');
        $fullEvents = Event::get()->filter(function($event) {
            return $event->isEventFull();
        })->count();
        $upcomingEvents = Event::where('event_date', '>=', now())->count();
        $pastEvents = Event::where('event_date', '<', now())->count();

        // Category statistics
        $categoryStats = Event::selectRaw('category, COUNT(*) as count, SUM(capacity) as total_capacity')
            ->groupBy('category')
            ->get();

        // Average participants per event
        $avgParticipants = $totalEvents > 0 ? round($totalParticipants / $totalEvents, 2) : 0;

        return response()->json([
            'success' => true,
            'summary' => [
                'total_events' => $totalEvents,
                'total_capacity' => $totalCapacity,
                'total_participants' => $totalParticipants,
                'avg_participants_per_event' => $avgParticipants,
                'capacity_utilization_percentage' => $totalCapacity > 0 ? round(($totalParticipants / $totalCapacity) * 100, 2) : 0,
            ],
            'event_status' => [
                'upcoming_events' => $upcomingEvents,
                'past_events' => $pastEvents,
                'full_events' => $fullEvents,
                'available_events' => $totalEvents - $fullEvents,
            ],
            'by_category' => $categoryStats->map(function($cat) {
                return [
                    'category' => $cat->category,
                    'total_events' => $cat->count,
                    'total_capacity' => $cat->total_capacity,
                    'avg_capacity' => round($cat->total_capacity / $cat->count, 2),
                ];
            }),
        ]);
    }

    /**
     * GET /api/events/{event}/stats
     * Get detailed event statistics
     */
    public function stats(Event $event)
    {
        $event->loadCount('participants');
        $allEvents = Event::count();
        $allParticipants = Event::withCount('participants')->get()->sum('participants_count');

        return response()->json([
            'success' => true,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'category' => $event->category,
                'capacity' => $event->capacity,
                'event_date' => $event->event_date,
            ],
            'participation' => [
                'registered_participants' => $event->participants_count,
                'remaining_slots' => $event->capacity - $event->participants_count,
                'capacity_percentage' => round(($event->participants_count / $event->capacity) * 100, 2),
                'is_full' => $event->isEventFull(),
            ],
            'system_stats' => [
                'event_position' => 'Position: ' . ($allEvents > 0 ? '1 of ' . $allEvents : 'N/A'),
                'total_system_participants' => $allParticipants,
                'event_share_of_participants' => $allParticipants > 0 ? round(($event->participants_count / $allParticipants) * 100, 2) : 0,
            ]
        ]);
    }
}
