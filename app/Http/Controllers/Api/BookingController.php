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

        try {
            DB::beginTransaction();
            $booking->update($request->validated());
            DB::commit();
            BookingConfirmed::dispatchIf($booking->status == 'confirmed', $booking)
                ->afterCommit();

            return response()->json([
                'success' => true,
                'booking' => $booking->fresh(),
                'message' => 'Booking updated successfully',
            ]);
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }

    }
}
