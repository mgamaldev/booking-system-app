<?php

use App\Models\Booking;
use Tests\TestCase;

class BookingFactoryTest   extends TestCase
{

    public function test_BookingFactoryReturnsCorrectBookingFactoryClass()
    {
        $data = [
            'type' => 'one-on-one',
            'slot_id' => 1,
            'customer_id' => 1,
        ];

        $bookingFactoryClass = \App\Factories\BookingFactory::resolve($data['type']);;

        $this->assertInstanceOf(\App\Factories\OneToOneBookingFactory::class, $bookingFactoryClass);
    }


    public function test_BookingFactoryReturnsCorrectBookingFactoryClassForGroupBooking()
    {
        $data = [
            'type' => 'group',
            'slot_id' => 1,
            'customer_id' => 1,
        ];

        $bookingFactoryClass = \App\Factories\BookingFactory::resolve($data['type']);;
        $this->assertInstanceOf(\App\Factories\GroupBookingFactory::class, $bookingFactoryClass);
    }



}
