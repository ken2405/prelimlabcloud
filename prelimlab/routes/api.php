<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ROUTES (No token required)
// ==========================================

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Event Viewing
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);


// ==========================================
// PROTECTED ROUTES (Requires Sanctum Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Authenticated User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================================
    // Event Management (CRUD)
    // ==========================================

    
    // List & search events
    Route::get('/events', [EventController::class, 'index']);

    // Create new event
    Route::post('/events', [EventController::class, 'store']);

    // Show single event
    Route::get('/events/{event}', [EventController::class, 'show']);

    // Update an event
    Route::put('/events/{event}', [EventController::class, 'update']);

    // Delete an event
    Route::delete('/events/{event}', [EventController::class, 'destroy']);


    

    Route::post('events/{event}/attend', [EventController::class, 'addAttendee']);

    // Event Statistics (Capacity Info)
    Route::get('/events/{event}/stats', [EventController::class, 'stats']);

    // Participant Registration (Capacity Controlled)
    Route::post('/events/{event}/register', [ParticipantController::class, 'register']);

});

