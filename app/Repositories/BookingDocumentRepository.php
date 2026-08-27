<?php

namespace App\Repositories;

use App\Models\BookingDocument;
use App\Repositories\Interfaces\BookingDocumentRepositoryInterface;

class BookingDocumentRepository implements BookingDocumentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BookingDocument
    {
        /** @var BookingDocument $document */
        $document = BookingDocument::query()->create($data);

        return $document;
    }
}
