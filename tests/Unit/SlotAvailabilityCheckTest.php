<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Resource;
use App\Models\Slot;
use App\Strategies\BookingStrategies\OneToOneBookingStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function oneToOneBookingData(?Slot $slot = null, ?Customer $customer = null, ?Resource $resource = null): array
{
    return [
        'type' => 'one-on-one',
        'slot_id' => ($slot ?? Slot::factory()->create())->id,
        'customer_id' => ($customer ?? Customer::factory()->create())->id,
        'resource_id' => ($resource ?? Resource::factory()->create())->id,
        'status' => 'pending',
    ];
}

test('it creates a booking when the slot is available', function () {
    $data = oneToOneBookingData();

    $booking = (new OneToOneBookingStrategy)->createBooking($data);

    expect($booking)
        ->type->toBe('one-on-one')
        ->slot_id->toBe($data['slot_id']);

    $this->assertDatabaseHas('bookings', $data);
});

test('it rejects an unavailable slot', function (string $blockingStatus) {
    $slot = Slot::factory()->create();
    $customer = Customer::factory()->create();
    $resource = Resource::factory()->create();

    Booking::query()->create([
        'type' => 'one-on-one',
        'slot_id' => $slot->id,
        'customer_id' => $customer->id,
        'resource_id' => $resource->id,
        'status' => $blockingStatus,
    ]);

    $strategy = new OneToOneBookingStrategy;

    expect(fn () => $strategy->createBooking(oneToOneBookingData($slot)))
        ->toThrow(Exception::class, 'Slot is not available')
        ->and(Booking::query()->where('slot_id', $slot->id)->count())->toBe(1);

})->with(['pending', 'confirmed']);

test('it allows a slot when the existing booking is canceled', function () {
    $slot = Slot::factory()->create();

    Booking::factory()->create([
        'type' => 'one-on-one',
        'slot_id' => $slot->id,
        'status' => 'canceled',
    ]);

    $booking = (new OneToOneBookingStrategy)->createBooking(oneToOneBookingData($slot));

    expect($booking->slot_id)->toBe($slot->id)
        ->and(Booking::query()->where('slot_id', $slot->id)->count())->toBe(2);
});
