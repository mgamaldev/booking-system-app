<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingConfirmed;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function update(Request $request, Booking $booking)
    {
        DB::transaction(function () use ($request, $booking) {
            $booking->update($request->all());

            if ($booking->status === 'confirmed') {
                BookingConfirmed::dispatch($booking);
            }
        });

        return response()->json([
            'success' => true,
            'booking' => $booking->fresh(),
            'message' => 'Booking updated successfully',
        ]);
    }
}
