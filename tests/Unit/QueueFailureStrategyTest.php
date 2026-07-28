<?php

use App\Listeners\SendFailedJobAlert;
use App\Models\Booking;
use App\Notifications\BookingConfirmationNotification;
use App\Notifications\BookingReminderNotification;
use App\Notifications\FailedQueueJobNotification;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('booking confirmation notification has a bounded retry policy', function () {
    $notification = new BookingConfirmationNotification(Booking::factory()->make());

    expect($notification->tries)->toBe(5)
        ->and($notification->backoff)->toBe([10, 30, 60, 120]);
});

test('booking reminder notification has a bounded retry policy', function () {
    $notification = new BookingReminderNotification(Booking::factory()->make());

    expect($notification->tries)->toBe(3)
        ->and($notification->backoff)->toBe([60, 300, 900]);
});

test('booking confirmation notification logs structured context when it fails permanently', function () {
    $booking = Booking::factory()->create();
    $exception = new RuntimeException('SMTP timeout');

    Log::shouldReceive('error')
        ->once()
        ->with('Booking confirmation notification failed permanently', Mockery::on(
            fn (array $context): bool => $context['job'] === BookingConfirmationNotification::class
                && $context['booking_id'] === $booking->id
                && $context['customer_id'] === $booking->customer_id
                && $context['customer_email'] === $booking->customer->email
                && $context['exception'] === RuntimeException::class
                && $context['message'] === 'SMTP timeout'
        ));

    (new BookingConfirmationNotification($booking))->failed($exception);
});

test('booking reminder notification logs structured context when it fails permanently', function () {
    $booking = Booking::factory()->create();
    $exception = new RuntimeException('Mail transport rejected message');

    Log::shouldReceive('error')
        ->once()
        ->with('Booking reminder notification failed permanently', Mockery::on(
            fn (array $context): bool => $context['job'] === BookingReminderNotification::class
                && $context['booking_id'] === $booking->id
                && $context['customer_id'] === $booking->customer_id
                && $context['customer_email'] === $booking->customer->email
                && $context['slot_id'] === $booking->slot_id
                && $context['slot_date']->isSameDay($booking->slot->date)
                && $context['slot_start_time'] === $booking->slot->start_time
                && $context['exception'] === RuntimeException::class
                && $context['message'] === 'Mail transport rejected message'
        ));

    (new BookingReminderNotification($booking))->failed($exception);
});

test('failed job listener logs critical context and sends the configured alert', function () {
    config(['queue.failed_alerts.mail_to' => 'ops@example.com']);

    Notification::fake();

    $job = Mockery::mock(Job::class);
    $job->shouldReceive('getQueue')->once()->andReturn('default');
    $job->shouldReceive('getJobId')->once()->andReturn('123');
    $job->shouldReceive('uuid')->once()->andReturn('failed-job-uuid');
    $job->shouldReceive('resolveName')->once()->andReturn(BookingConfirmationNotification::class);
    $job->shouldReceive('attempts')->once()->andReturn(5);

    Log::shouldReceive('critical')
        ->once()
        ->with('Queue job landed in failed_jobs', Mockery::on(
            fn (array $context): bool => $context['connection'] === 'database'
                && $context['queue'] === 'default'
                && $context['job_id'] === '123'
                && $context['uuid'] === 'failed-job-uuid'
                && $context['name'] === BookingConfirmationNotification::class
                && $context['attempts'] === 5
                && $context['exception'] === RuntimeException::class
                && $context['message'] === 'Final failure'
        ));

    app(SendFailedJobAlert::class)->handle(new JobFailed(
        'database',
        $job,
        new RuntimeException('Final failure'),
    ));

    Notification::assertSentOnDemand(
        FailedQueueJobNotification::class,
        fn (FailedQueueJobNotification $notification, array $channels, object $notifiable): bool => in_array('mail', $channels, true)
            && $notifiable->routeNotificationFor('mail') === 'ops@example.com'
    );
});
