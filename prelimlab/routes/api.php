<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;

// Users
Route::get('/users', [UserController::class, 'index']);

// Events
Route::get('/events', [EventController::class, 'index']);

// Registrations
Route::get('/registrations', [RegistrationController::class, 'index']);