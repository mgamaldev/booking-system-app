<?php

use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Resource;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('it dispatches booking confirmation job after a booking is confirmed', function () {
    Queue::fake();

    $booking = Booking::factory()->create([
        'status' => 'pending',
    ]);

    $this->post(route('bookings.update', $booking), [
        'status' => 'confirmed',
    ])->assertOk();

    Queue::assertPushed(SendBookingConfirmation::class, function (SendBookingConfirmation $job) use ($booking) {
        return $job->booking->is($booking)
            && $job->queue === 'bookings'
            && $job->afterCommit === true
            && $job->tries === 3
            && $job->backoff === [10, 30, 60];
    });
});

test('it creates api bookings through the service as pending and does not dispatch confirmation', function () {
    Queue::fake();

    $response = $this->postJson(route('bookings.store'), [
        'customer_id' => Customer::factory()->create()->id,
        'resource_id' => Resource::factory()->create()->id,
        'slot_id' => Slot::factory()->create()->id,
        'status' => 'confirmed',
        'type' => 'one-on-one',
    ])->assertCreated();

    $booking = Booking::query()->findOrFail($response->json('booking.id'));

    expect($booking->status)->toBe('pending');
    Queue::assertNotPushed(SendBookingConfirmation::class);
});

test('it rejects api booking creation when the slot is already unavailable', function () {
    Queue::fake();

    $slot = Slot::factory()->create();

    Booking::factory()->create([
        'slot_id' => $slot->id,
        'status' => 'confirmed',
        'type' => 'one-on-one',
    ]);

    $this->postJson(route('bookings.store'), [
        'customer_id' => Customer::factory()->create()->id,
        'resource_id' => Resource::factory()->create()->id,
        'slot_id' => $slot->id,
        'status' => 'confirmed',
        'type' => 'one-on-one',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slot_id');

    expect(Booking::query()->where('slot_id', $slot->id)->count())->toBe(1);
    Queue::assertNotPushed(SendBookingConfirmation::class);
});
