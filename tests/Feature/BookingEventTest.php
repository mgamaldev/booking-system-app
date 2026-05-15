<?php

namespace Tests\Feature;

use App\Events\BookingConfirmed;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BookingEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_event_is_fired_when_booking_is_updated()
    {
        Event::fake();
        $booking = Booking::factory()->create();
        $this->post(route('bookings.update', $booking), ['status' => 'confirmed']);
        Event::assertDispatched(BookingConfirmed::class);

    }

    public function test_booking_event_is_not_fired_when_booking_is_not_updated() {
        Event::fake();
        $booking = Booking::factory()->create();
        $this->post(route('bookings.update', $booking), ['status' => 'pending']);
        Event::assertNotDispatched(BookingConfirmed::class);
    }
}
