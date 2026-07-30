<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ReturnsApiResponses;
use App\Http\Controllers\Controller;
use App\Models\BookingDocument;
use Illuminate\Support\Facades\Storage;

class BookingDocumentTemporaryUrlController extends Controller
{
    use ReturnsApiResponses;

    public function __invoke(BookingDocument $booking_document)
    {
        $bookingDocument = $booking_document;

        $bookingDocument->loadMissing('booking.customer');

        abort_unless(
            $bookingDocument->booking->customer->email === request()->user()->email,
            403
        );

        $expiresInMinutes = (int) config('filesystems.disks.documents.temporary_url_expiration_minutes', 5);

        $url = Storage::disk('documents')->temporaryUrl(
            $bookingDocument->key,
            now()->addMinutes($expiresInMinutes)
        );

        return $this->successResponse([
            'url' => $url,
        ]);
    }
}
