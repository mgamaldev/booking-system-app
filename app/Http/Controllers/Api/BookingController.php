<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $booking = DB::transaction(function () use ($request, $booking) {
            $booking->update($request->validated());

            $booking->refresh();

            if ($booking->status === 'confirmed') {
                BookingConfirmed::dispatch($booking);
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
