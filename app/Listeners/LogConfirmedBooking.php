<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;

class LogConfirmedBooking
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(BookingConfirmed $event): void
    {
        logger('Booking confirmed: '.$event->booking->id);
    }
}
