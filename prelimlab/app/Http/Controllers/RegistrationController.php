<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource (with filters and pagination).
     */
    public function index(Request $request)
    {
        $registrations = Registration::query()
            ->with(['user:id,name,email', 'event:id,title,start_date'])
            ->when($request->event_id, function ($query, $eventId) {
                $query->where('event_id', $eventId);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->status, function ($query, $status) {
                if (in_array($status, ['registered', 'checked_in'])) {
                    $query->where('status', $status);
                }
            })
            ->paginate($request->input('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $registrations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming request data
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => [
                'required',
                'exists:users,id',
                // Prevent duplicate registrations for the same user and event
                Rule::unique('registrations')->where(function ($query) use ($request) {
                    return $query->where('event_id', $request->event_id);
                })
            ],
            'status' => 'sometimes|in:registered,checked_in',
        ]);

        // Default status if not provided
        $validatedData['status'] = $validatedData['status'] ?? 'registered';

        // 2. Create the registration
        $registration = Registration::create($validatedData);

        // Load relationships so the response includes user and event details
        $registration->load(['user:id,name,email', 'event:id,title,start_date']);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => $registration
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $registration = Registration::with(['user:id,name,email', 'event:id,title,start_date'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $registration
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $registration = Registration::findOrFail($id);

        // Usually, the only thing you update on a registration is the status (e.g., checking them in)
        $validatedData = $request->validate([
            'status' => 'required|in:registered,checked_in',
        ]);

        $registration->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Registration status updated successfully',
            'data' => $registration
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $registration = Registration::findOrFail($id);
        
        $registration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registration deleted successfully'
        ]);
    }
}