<?php

use App\Jobs\SendBookingReminder;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Resource;
use App\Models\Slot;
use App\Notifications\BookingReminderNotification;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('booking reminders are sent exactly once only for bookings inside the reminder window', function () {
    Notification::fake();
    Carbon::setTestNow(Carbon::parse('2026-07-15 09:00:00'));

    $resource = Resource::factory()->create();

    $insideFirst = makeBooking($resource, Carbon::now()->addDay()->toDateString(), '09:00:00');
    $insideSecond = makeBooking($resource, Carbon::now()->addDay()->toDateString(), '11:00:00');
    $outsideSoon = makeBooking($resource, Carbon::now()->addDays(2)->toDateString(), '09:00:00');
    $outsidePast = makeBooking($resource, Carbon::now()->subDay()->toDateString(), '09:00:00');

    $job = new SendBookingReminder(1);
    $job->handle(app(BookingService::class));

    expect(Notification::sent($insideFirst->customer, BookingReminderNotification::class))->toHaveCount(1);
    expect(Notification::sent($insideSecond->customer, BookingReminderNotification::class))->toHaveCount(1);
    expect(Notification::sent($outsideSoon->customer, BookingReminderNotification::class))->toHaveCount(0);
    expect(Notification::sent($outsidePast->customer, BookingReminderNotification::class))->toHaveCount(0);

    $job->handle(app(BookingService::class));

    expect(Notification::sent($insideFirst->customer, BookingReminderNotification::class))->toHaveCount(1);
    expect(Notification::sent($insideSecond->customer, BookingReminderNotification::class))->toHaveCount(1);

    expect($insideFirst->fresh()->reminder_sent_at)->not->toBeNull();
    expect($insideSecond->fresh()->reminder_sent_at)->not->toBeNull();
    expect($outsideSoon->fresh()->reminder_sent_at)->toBeNull();
    expect($outsidePast->fresh()->reminder_sent_at)->toBeNull();

    Carbon::setTestNow();
});

function makeBooking(Resource $resource, string $date, string $time): Booking
{
    $customer = Customer::factory()->create();
    $slot = Slot::factory()->create([
        'date' => $date,
        'start_time' => $time,
        'end_time' => Carbon::parse($time)->addHour()->format('H:i:s'),
    ]);

    return Booking::query()->create([
        'customer_id' => $customer->id,
        'resource_id' => $resource->id,
        'slot_id' => $slot->id,
        'status' => 'confirmed',
    ]);
}
