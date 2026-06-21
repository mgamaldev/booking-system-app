<?php

namespace App\Services;

use App\Models\Booking;

final readonly class BookingCancellationResult
{
    public function __construct(
        public Booking $booking,
        public float $fee,
    ) {}
}
