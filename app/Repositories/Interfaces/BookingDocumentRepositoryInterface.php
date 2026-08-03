<?php

namespace App\Repositories\Interfaces;

use App\Models\BookingDocument;

interface BookingDocumentRepositoryInterface
{
    public function create(array $data): BookingDocument;
}
