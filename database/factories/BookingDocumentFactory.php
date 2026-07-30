<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingDocument>
 */
class BookingDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'disk' => 'documents',
            'key' => 'booking-documents/'.$this->faker->uuid().'.pdf',
            'original_name' => 'document.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
        ];
    }
}
