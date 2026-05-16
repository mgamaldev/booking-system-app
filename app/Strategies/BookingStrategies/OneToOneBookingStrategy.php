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

    private function isSlotAvailability($slotId)
    {
        return Booking::whereHas('slot', function ($query) use ($slotId) {
            return $query->where('id', $slotId);
        })->doesntExist();
    }
}
