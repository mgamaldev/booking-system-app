<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Resource;
use App\Models\Slot;
use App\ValueObjects\SlotDuration;
use Carbon\Carbon;
use Tests\TestCase;

class BookingDurationTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_booking_duration_attribute_is_instance_from_slot_duration_class(): void
    {
        $slot = Slot::factory()->create();
        $booking = Booking::create([
            'customer_id' => Customer::factory()->create()->id,
            'resource_id' => Resource::factory()->create()->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);
        $this->assertIsInt($booking->duration->minutes);
        $this->assertInstanceOf(SlotDuration::class, $booking->duration);
    }

    public function test_booking_duration_throws_exception_for_invalid_values(): void
    {
        $slot = Slot::create([
            'start_time' => '13:00:00',
            'end_time' => '12:00:00',
            'date' => '2024-01-01',
            'status' => 'active',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $diff = Carbon::parse($slot->start_time)->diffInMinutes(
            Carbon::parse($slot->end_time),
        );

        new SlotDuration($diff);
    }
}
