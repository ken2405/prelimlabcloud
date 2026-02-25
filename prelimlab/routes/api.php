<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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