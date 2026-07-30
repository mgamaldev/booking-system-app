<?php

namespace App\Services\Contracts;

use App\Models\Booking;
use App\Models\BookingDocument;
use Illuminate\Http\UploadedFile;

interface FilesUploadServiceInterface
{
    public function uploadBookingDocument(Booking $booking, UploadedFile $file): BookingDocument;
}
