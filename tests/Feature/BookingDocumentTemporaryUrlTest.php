<?php

use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('authorized user receives a temporary document url', function () {
    Storage::fake('documents');

    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'email' => $user->email,
    ]);
    $booking = Booking::factory()->create([
        'customer_id' => $customer->id,
    ]);
    $document = BookingDocument::factory()->create([
        'booking_id' => $booking->id,
        'key' => 'booking-documents/receipt.pdf',
    ]);

    $this->actingAs($user)
        ->getJson(route('booking-documents.temporary-url', $document))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'url',
            ],
        ])
        ->assertJsonPath('data.url', fn (string $url) => str_contains($url, 'receipt.pdf')
            && str_contains($url, 'expiration='));
});

test('unauthorized user is refused before a temporary document url is generated', function () {
    Storage::fake('documents');
    Storage::disk('documents')->buildTemporaryUrlsUsing(function () {
        throw new RuntimeException('Temporary URL should not be generated for unauthorized users.');
    });

    $customer = Customer::factory()->create([
        'email' => 'owner@example.test',
    ]);
    $booking = Booking::factory()->create([
        'customer_id' => $customer->id,
    ]);
    $document = BookingDocument::factory()->create([
        'booking_id' => $booking->id,
    ]);
    $otherUser = User::factory()->create([
        'email' => 'other@example.test',
    ]);

    $this->actingAs($otherUser)
        ->getJson(route('booking-documents.temporary-url', $document))
        ->assertForbidden();
});
