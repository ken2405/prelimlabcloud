<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public event listing
Route::get('/events', [PublicEventController::class, 'index'])->name('events.index');

// Single event page (optional)
Route::get('/events/{id}', [PublicEventController::class, 'show'])->name('events.show');
