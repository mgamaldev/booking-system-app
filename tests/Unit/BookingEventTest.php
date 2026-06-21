<?php

namespace Tests\Unit;

use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_event_is_fired_when_booking_is_updated()
    {
        Queue::fake();

        $booking = Booking::factory()->create();
        $this->post(route('bookings.update', $booking), ['status' => 'confirmed'])
            ->assertOk();

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($booking) {
            return $job->notification instanceof BookingConfirmationNotification
                && $job->notification->booking->is($booking);
        });

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
