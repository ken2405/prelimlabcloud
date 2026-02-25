<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ParticipantController;

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

 Route::apiResource('events', EventController::class);
Route::post('events/{event}/attend', [EventController::class, 'addAttendee']);

    // Event Statistics (Capacity Info)
    Route::get('/events/{event}/stats', [EventController::class, 'stats']);

    // Participant Registration (Capacity Controlled)
    Route::post('/events/{event}/register', [ParticipantController::class, 'register']);

});
