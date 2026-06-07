<?php

namespace App\Strategies\BookingStrategies;

use App\Models\Booking;

class OneToOneBookingStrategy implements BookingStrategyInterface
{
    /**
     * @throws \Exception
     */

    /**
     * @throws \Exception
     */
    public function createBooking(array $data): Booking
    {
        if (! $this->isSlotAvailability($data['slot_id'])) {
            throw new \Exception('Slot is not available');
        }

        return Booking::create($data);

    }

    private function isSlotAvailability($slotId): bool
    {
        return Booking::query()
            ->where('slot_id', $slotId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->doesntExist();
    }
}
