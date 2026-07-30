<?php

namespace Tests\Unit;

use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_confirmation_job_is_pushed_when_booking_is_updated()
    {
        Queue::fake();

        $booking = Booking::factory()->create();
        $this->actingAs(Customer::query()->findOrFail($booking->customer_id), 'sanctum')
            ->post(route('bookings.update', $booking), ['status' => 'confirmed'])
            ->assertOk();

        Queue::assertPushed(SendBookingConfirmation::class, function (SendBookingConfirmation $job) use ($booking) {
            return $job->booking->is($booking)
                && $job->afterCommit === true;
        });

    }

    public function test_booking_confirmation_job_is_not_pushed_when_booking_is_not_confirmed()
    {
        Queue::fake();

        $booking = Booking::factory()->create();
        $this->actingAs(Customer::query()->findOrFail($booking->customer_id), 'sanctum')
            ->post(route('bookings.update', $booking), ['status' => 'pending']);

        Queue::assertNotPushed(SendBookingConfirmation::class);
    }

    public function test_booking_confirmation_job_is_marked_to_dispatch_after_commit(): void
    {
        Queue::fake();

        $booking = Booking::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs(Customer::query()->findOrFail($booking->customer_id), 'sanctum')
            ->post(route('bookings.update', $booking), [
                'status' => 'confirmed',
            ]);

        Queue::assertPushed(SendBookingConfirmation::class, function (SendBookingConfirmation $job) {
            return $job->afterCommit === true;
        });
    }
}
