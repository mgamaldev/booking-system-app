<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\S3UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function (Request $request) {
    return response()->json(['message' => 'test']);
});

Route::post('register', [AuthController::class, 'register'])->name('auth.register');
Route::post('login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('booking', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('booking/{booking}/update', [BookingController::class, 'update'])->name('bookings.update');
    Route::post('booking/{booking}/documents', [S3UploadController::class, 'upload'])->name('bookings.upload');
});
