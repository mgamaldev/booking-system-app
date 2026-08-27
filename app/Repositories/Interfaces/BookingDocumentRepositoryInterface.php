<?php

namespace App\Repositories\Interfaces;

use App\Models\BookingDocument;

interface BookingDocumentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BookingDocument;
}
