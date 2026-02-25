<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ParticipantController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==========================================
// PUBLIC ROUTES (No token required)
// ==========================================

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Event Viewing (Allow anyone to see available events)
Route::get('/events', [EventController::class, 'index']);


// ==========================================
// PROTECTED ROUTES (Requires Sanctum Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Get the currently authenticated user's details
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Event Management
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/stats', [EventController::class, 'stats']); // Note: Place specific routes like /stats above dynamic ones if you add /events/{event} later

    // Participant Registration (Registering for a specific event)
    Route::post('/events/{event}/register', [ParticipantController::class, 'register']);

});
