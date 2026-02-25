<?php

use App\Http\Controllers\UserController;
use EventController;
use Illuminate\Support\Facades\Route;

// Users
Route::get('/users', [UserController::class, 'index']);

// Events
Route::apiResource('events', EventController::class);