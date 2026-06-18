<?php

namespace App\Repositories\Interfaces;

use App\Models\Booking;

interface BookingCancellationRepositoryInterface
{
    public function findForCancellation(int $bookingId): Booking;

    public function cancel(Booking $booking): Booking;
}
