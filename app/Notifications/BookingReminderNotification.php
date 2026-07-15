<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(public Booking $booking)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Reminder')
            ->greeting('Hello '.$this->booking->customer->name)
            ->line('This is a reminder for your upcoming booking.')
            ->line('Booking date: '.$this->booking->slot->date)
            ->line('Start time: '.$this->booking->slot->start_time)
            ->action('View Booking', url('/bookings/'.$this->booking->id))
            ->line('Thank you for booking with us.');
    }

    public function failed(Throwable $e): void
    {
        Log::error('Booking reminder notification failed permanently', [
            'job' => self::class,
            'booking_id' => $this->booking->id,
            'customer_id' => $this->booking->customer_id,
            'customer_email' => $this->booking->customer->email,
            'slot_id' => $this->booking->slot_id,
            'slot_date' => $this->booking->slot->date,
            'slot_start_time' => $this->booking->slot->start_time,
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);
    }
}
