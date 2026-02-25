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

// Statistics
Route::get('/stats', [EventController::class, 'globalStats']);
Route::get('/events/{event}/stats', [EventController::class, 'stats']);

// Public Participant Viewing & Registration
Route::get('/events/{event}/participants', [ParticipantController::class, 'index']);
Route::get('/events/{event}/participants/{participant}', [ParticipantController::class, 'show']);
Route::post('/events/{event}/register', [ParticipantController::class, 'register']);


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

    // Event CRUD Operations
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    // Participant Management (Update & Delete)
    Route::put('/events/{event}/participants/{participant}', [ParticipantController::class, 'update']);
    Route::delete('/events/{event}/participants/{participant}', [ParticipantController::class, 'destroy']);

});
