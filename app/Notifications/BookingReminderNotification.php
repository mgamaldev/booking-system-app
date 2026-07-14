<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
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
}
