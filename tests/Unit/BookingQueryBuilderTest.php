<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Resource;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingQueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_returns_only_confirmed_bookings(): void
    {
        $confirmed = Booking::factory()->create(['status' => 'confirmed']);
        Booking::factory()->create(['status' => 'pending']);
        Booking::factory()->create(['status' => 'canceled']);

        $bookings = Booking::query()->confirmed()->get();

        $this->assertCount(1, $bookings);
        $this->assertTrue($bookings->first()->is($confirmed));
        $this->assertSame('confirmed', $bookings->first()->status);
    }

    public function test_by_customer_filters_by_customer_id(): void
    {
        $targetCustomer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $resource = Resource::factory()->create();
        $slot = Slot::factory()->create();

        $targetBooking = Booking::factory()->create([
            'customer_id' => $targetCustomer->id,
            'resource_id' => $resource->id,
            'slot_id' => $slot->id,
        ]);

        Booking::factory()->create([
            'customer_id' => $otherCustomer->id,
            'resource_id' => $resource->id,
            'slot_id' => $slot->id,
        ]);

        $bookings = Booking::query()->byCustomer($targetCustomer->id)->get();

        $this->assertCount(1, $bookings);
        $this->assertTrue($bookings->first()->is($targetBooking));
    }

    public function test_slot_filters_by_slot_id(): void
    {
        $customer = Customer::factory()->create();
        $resource = Resource::factory()->create();
        $targetSlot = Slot::factory()->create();
        $otherSlot = Slot::factory()->create();

        $targetBooking = Booking::factory()->create([
            'customer_id' => $customer->id,
            'resource_id' => $resource->id,
            'slot_id' => $targetSlot->id,
        ]);

        Booking::factory()->create([
            'customer_id' => $customer->id,
            'resource_id' => $resource->id,
            'slot_id' => $otherSlot->id,
        ]);

        $bookings = Booking::query()->bySlot($targetSlot->id)->get();

        $this->assertCount(1, $bookings);
        $this->assertTrue($bookings->first()->is($targetBooking));
    }

    public function test_with_relations_sets_expected_eager_loads(): void
    {
        Booking::factory()->count(3)->create();
        $query = Booking::query()->withRelations();
        $eagerLoads = array_keys($query->get()->first()->getRelations());
        $this->assertContains('customer', $eagerLoads);
        $this->assertContains('slot', $eagerLoads);
        $this->assertContains('resource', $eagerLoads);
    }

    public function test_paginate_appends_query_parameters(): void
    {
        Booking::factory()->count(3)->create();

        $query = Booking::query()->paginate(2);

        $array = $query->appends(request()->query())->toArray();

        $this->assertArrayHasKey('last_page', $array);
    }

    public function test_upcoming__booking_filter(): void
    {
        $slot = Slot::factory()->create(
            [
                'date' => now()->addDay()->toDateString(),
            ]
        );

        Booking::factory()->create(['slot_id' => $slot->id]);

        $bookings = Booking::query()->upcoming()->get();

        $this->assertCount(1, $bookings);

    }
}
