<?php

use App\Models\Customer;
use App\Models\Resource;
use App\Models\Slot;
use App\Strategies\BookingStrategies\BookingStrategyResolver;
use App\Strategies\BookingStrategies\GroupBookingStrategy;
use App\Strategies\BookingStrategies\OneToOneBookingStrategy;
use App\Strategies\BookingStrategies\RecurringBookingStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingStrategiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_strategy_binds_for_one_to_one_booking(): void
    {
        $type = 'one-on-one';

        $strategy = BookingStrategyResolver::resolve($type);

        $this->assertInstanceOf(OneToOneBookingStrategy::class, $strategy);
    }

    public function test_booking_strategy_binds_for_group_booking(): void
    {
        $type = 'group';

        $strategy = BookingStrategyResolver::resolve($type);

        $this->assertInstanceOf(GroupBookingStrategy::class, $strategy);
    }

    public function test_booking_strategy_binds_for_recurring_booking(): void
    {
        $type = 'recurring';

        $strategy = BookingStrategyResolver::resolve($type);

        $this->assertInstanceOf(RecurringBookingStrategy::class, $strategy);
    }

    public function test_booking_strategy_returns_inserted_booking()
    {
        $data = [
            'type' => 'one-on-one',
            'slot_id' => Slot::factory()->create()->id,
            'customer_id' => Customer::factory()->create()->id,
            'resource_id' => Resource::factory()->create()->id,
        ];

        $strategy = BookingStrategyResolver::resolve($data['type']);
        $booking = $strategy->createBooking([
            'type' => $data['type'],
            'slot_id' => $data['slot_id'],
            'customer_id' => $data['customer_id'],
            'resource_id' => $data['resource_id'],
            'status' => 'pending',

        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'type' => 'one-on-one']);
    }

    public function test_recurring_booking_strategy_throw_exception_with_invalid_dates()
    {
        $data = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-01',
            'recurrence_rule' => 'weekly',
            'start_time' => '13:00:00',
        ];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('End date must be after start date for recurring booking.');
        $strategy = BookingStrategyResolver::resolve('recurring');
        $strategy->createBooking($data);
    }
}
