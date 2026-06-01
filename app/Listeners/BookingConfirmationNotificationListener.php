<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Support\Facades\Notification;

class BookingConfirmationNotificationListener
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
        Notification::send($event->booking->customer, new BookingConfirmationNotification($event->booking));
    }
}
