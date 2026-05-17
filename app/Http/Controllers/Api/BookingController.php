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
        DB::beginTransaction();

        try {
            $booking->update($request->validated());

            $booking->refresh();

            if ($booking->status === 'confirmed') {
                BookingConfirmed::dispatch($booking);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'booking' => $booking->fresh(),
                'message' => 'Booking updated successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
