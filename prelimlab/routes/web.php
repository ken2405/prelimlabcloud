<?php

use App\Http\Controllers\{AuthController, EventController, ParticipantController, PublicEventController};
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public event listing
Route::get('/events', [EventController::class, 'index']);

Route::get('/events', [PublicEventController::class, 'index'])->name('events.index');
Route::get('/events/{id}', [PublicEventController::class, 'show'])->name('events.show');
