<?php

namespace App\Jobs;

use App\Notifications\BookingReminderNotification;
use App\Services\BookingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendBookingReminder implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $days_before_reminder = 1)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(BookingService $bookingService): void
    {
        $bookings = $bookingService->claimBookingReminders($this->days_before_reminder);

        foreach ($bookings as $booking) {
            Notification::send($booking->customer, new BookingReminderNotification($booking));
        }
    }
}
