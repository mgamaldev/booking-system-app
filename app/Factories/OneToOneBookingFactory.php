<?php

namespace App\Factories;

use App\Models\Booking;
use App\Models\Slot;

class OneToOneBookingFactory implements BookingFactoryInterface {
    /**
     * @throws \Exception
     */
    public function create(array $data): Booking
    {
        if (!$this->isSlotAvailability($data['slot_id'])) {
            throw new \Exception('Slot is not available');
        }

       $this->checkSlotAndCustomerCount($data['customer_id'], $data['slot_id']);

        return  Booking::create($data);

    }



    private function isSlotAvailability($slotId) {
        return Booking::whereHas('slot' , function ($query) use ($slotId) {
            return  $query->where('id' , $slotId);
        })->doesntExist();
    }


    /**
     * @throws \Exception
     */
    private function checkSlotAndCustomerCount($customerId, $slotId): void
    {
        if (count($customerId) > 1) {
            throw new \Exception('More one Customer in this type is not available');
        }

        if (count($slotId) > 1) {
            throw new \Exception('More one Slot in this type is not available');
        }
    }
}
