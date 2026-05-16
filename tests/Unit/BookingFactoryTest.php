<?php

use App\Factories\BookingFactory;
use App\Factories\GroupBookingFactory;
use App\Factories\OneToOneBookingFactory;
use Tests\TestCase;

class BookingFactoryTest extends TestCase
{
    public function test_booking_factory_returns_correct_booking_factory_class()
    {
        $data = [
            'type' => 'one-on-one',
            'slot_id' => 1,
            'customer_id' => 1,
        ];

        $bookingFactoryClass = BookingFactory::resolve($data['type']);

        $this->assertInstanceOf(OneToOneBookingFactory::class, $bookingFactoryClass);
    }

    public function test_booking_factory_returns_correct_booking_factory_class_for_group_booking()
    {
        $data = [
            'type' => 'group',
            'slot_id' => 1,
            'customer_id' => 1,
        ];

        $bookingFactoryClass = BookingFactory::resolve($data['type']);
        $this->assertInstanceOf(GroupBookingFactory::class, $bookingFactoryClass);
    }
}
