<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailedQueueJobNotification extends Notification
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(private readonly array $context)
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
            ->error()
            ->subject('Queue job failed permanently')
            ->line('A queued job has landed in failed_jobs.')
            ->line('Job: '.$this->contextValue('name'))
            ->line('Queue: '.$this->contextValue('connection').'/'.$this->contextValue('queue'))
            ->line('Job ID: '.$this->contextValue('job_id'))
            ->line('UUID: '.$this->contextValue('uuid'))
            ->line('Attempts: '.$this->contextValue('attempts'))
            ->line('Exception: '.$this->contextValue('exception'))
            ->line('Message: '.$this->contextValue('message'));
    }

    private function contextValue(string $key): string
    {
        $value = $this->context[$key] ?? 'n/a';

        if (is_scalar($value)) {
            return (string) $value;
        }

        return 'n/a';
    }
}
