<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request)
    {
        $booking = DB::transaction(function () use ($request) {
            $booking = Booking::query()->create($request->validated());

            if ($booking->status === 'confirmed') {
                SendBookingConfirmation::dispatch($booking)->afterCommit();
            }

            return $booking->fresh();
        });

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Booking created successfully',
        ], 201);
    }

    /**
     * @throws \Throwable
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $booking = DB::transaction(function () use ($request, $booking) {
            $booking->update($request->validated());

            $booking->refresh();

            if ($booking->status === 'confirmed') {
                SendBookingConfirmation::dispatch($booking)->afterCommit();
            }

            return $booking->fresh();
        });

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Booking updated successfully',
        ]);
    }
}
