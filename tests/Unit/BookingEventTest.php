<?php

namespace Tests\Unit;

use App\Events\BookingConfirmed;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BookingEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_event_is_fired_when_booking_is_updated()
    {

        $booking = Booking::factory()->create();
        $this->post(route('bookings.update', $booking), ['status' => 'confirmed']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->customer_id]);

    }

    public function test_booking_event_is_not_fired_when_booking_is_not_updated()
    {
        Event::fake();
        $booking = Booking::factory()->create();
        $this->post(route('bookings.update', $booking), ['status' => 'pending']);
        Event::assertNotDispatched(BookingConfirmed::class);
    }



    /**
     * @throws \Throwable
     */
    public function test_booking_confirmed_event_is_dispatched_after_commit_only(): void
    {
        Event::fake([
            BookingConfirmed::class,
        ]);

        DB::beginTransaction();

        try {
            $booking = Booking::factory()->create([
                'status' => 'pending',
            ]);

            $this->post(route('bookings.update', $booking), [
                'status' => 'confirmed',
            ]);

            Event::assertNotDispatched(BookingConfirmed::class);

            DB::commit();

            Event::assertDispatched(BookingConfirmed::class);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
