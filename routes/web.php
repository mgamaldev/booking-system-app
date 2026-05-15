<?php

use App\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('booking/{booking}/update', [BookingController::class, 'update'])->name('bookings.update');
