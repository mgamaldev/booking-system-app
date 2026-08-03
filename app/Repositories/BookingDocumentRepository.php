<?php

namespace App\Repositories;

use App\Models\BookingDocument;
use App\Repositories\Interfaces\BookingDocumentRepositoryInterface;

class BookingDocumentRepository implements BookingDocumentRepositoryInterface
{
    public function create(array $data): BookingDocument
    {
        /** @var BookingDocument $document */
        $document = BookingDocument::query()->create($data);

        return $document;
    }
}
