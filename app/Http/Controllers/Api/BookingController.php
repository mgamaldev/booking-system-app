<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use App\Services\BookingService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request, BookingService $bookingService)
    {
        try {
            $booking = DB::transaction(function () use ($request, $bookingService) {
                $booking = $bookingService->createBooking($request->validated());

                if ($booking->status === 'confirmed') {
                    SendBookingConfirmation::dispatch($booking)->afterCommit();
                }

                return $booking->fresh();
            });
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'slot_id' => [$exception->getMessage()],
            ]);
        }

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
