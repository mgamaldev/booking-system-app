<?php

use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('it dispatches booking confirmed event when a booking is confirmed', function () {
    Event::fake([BookingConfirmed::class]);

    $booking = Booking::factory()->create([
        'status' => 'pending',
    ]);

    $this->post(route('bookings.update', $booking), [
        'status' => 'confirmed',
    ])->assertOk();

    Event::assertDispatched(BookingConfirmed::class, function (BookingConfirmed $event) use ($booking) {
        return $event->booking->is($booking);
    });
});

test('it queues booking confirmation notification when a booking is confirmed', function () {
    Queue::fake();

    $booking = Booking::factory()->create([
        'status' => 'pending',
    ]);

    $this->post(route('bookings.update', $booking), [
        'status' => 'confirmed',
    ])->assertOk();

    Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($booking) {
        return $job->notification instanceof BookingConfirmationNotification
            && $job->notification->booking->is($booking);
    });
});
