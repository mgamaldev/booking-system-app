<?php

use App\Models\Booking;

it('creates a booking when the slot has no pending or confirmed booking', function () {
    $repository = new InMemoryOneToOneBookingRepository(
        bookedSlotIds: [],
    );

    $strategy = new InjectableOneToOneBookingStrategy($repository);

    $booking = $strategy->createBooking([
        'type' => 'one-on-one',
        'slot_id' => 10,
        'customer_id' => 20,
        'resource_id' => 30,
        'status' => 'pending',
    ]);

    expect($booking->slot_id)->toBe(10)
        ->and($booking->type)->toBe('one-on-one')
        ->and($repository->createdBookings)->toHaveCount(1);
});

it('rejects a slot that is already booked', function () {
    $repository = new InMemoryOneToOneBookingRepository(
        bookedSlotIds: [10],
    );

    $strategy = new InjectableOneToOneBookingStrategy($repository);

    $strategy->createBooking([
        'type' => 'one-on-one',
        'slot_id' => 10,
        'customer_id' => 20,
        'resource_id' => 30,
        'status' => 'pending',
    ]);
})->throws(Exception::class, 'Slot is not available');

interface OneToOneBookingRepository
{
    public function hasActiveBookingForSlot(int $slotId): bool;

    public function create(array $data): Booking;
}

final class InjectableOneToOneBookingStrategy
{
    public function __construct(
        private readonly OneToOneBookingRepository $bookings,
    ) {}

    public function createBooking(array $data): Booking
    {
        if ($this->bookings->hasActiveBookingForSlot((int) $data['slot_id'])) {
            throw new Exception('Slot is not available');
        }

        return $this->bookings->create($data);
    }
}

final class InMemoryOneToOneBookingRepository implements OneToOneBookingRepository
{
    public array $createdBookings = [];

    public function __construct(
        private readonly array $bookedSlotIds,
    ) {}

    public function hasActiveBookingForSlot(int $slotId): bool
    {
        return in_array($slotId, $this->bookedSlotIds, true);
    }

    public function create(array $data): Booking
    {
        $this->createdBookings[] = $data;

        return new Booking($data);
    }
}
