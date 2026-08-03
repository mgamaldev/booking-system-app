<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDocument;
use App\Repositories\Interfaces\BookingDocumentRepositoryInterface;
use App\Services\Contracts\FilesUploadServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class S3FilesUploadService implements FilesUploadServiceInterface
{
    public function __construct(private BookingDocumentRepositoryInterface $bookingDocumentRepository) {}

    /**
     * @throws Throwable
     */
    public function uploadBookingDocument(Booking $booking, UploadedFile $file): BookingDocument
    {
        $disk = 'documents';
        $extension = $file->extension();
        $filename = Str::uuid().($extension ? ".{$extension}" : '');
        $directory = "bookings/{$booking->getKey()}/documents";
        $key = "{$directory}/{$filename}";
        $uploaded = false;

        DB::beginTransaction();

        try {
            $storedKey = Storage::disk($disk)->putFileAs($directory, $file, $filename);

            if ($storedKey === false) {
                throw new RuntimeException('The booking document could not be stored.');
            }

            $uploaded = true;

            $document = $this->bookingDocumentRepository->create([
                'booking_id' => $booking->getKey(),
                'disk' => $disk,
                'key' => $key,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($uploaded) {
                Storage::disk($disk)->delete($key);
            }

            throw $exception;
        }

        return $document;
    }
}
