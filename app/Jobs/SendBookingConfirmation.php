<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendBookingConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(public Booking $booking)
    {
        $this->queue = 'bookings';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->booking->loadMissing('customer');

        Notification::send(
            $this->booking->customer,
            new BookingConfirmationNotification($this->booking),
        );

        logger('Booking confirmed: '.$this->booking->id);
    }
}
