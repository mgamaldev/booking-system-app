<?php

namespace App\Repositories\Interfaces;

use App\Models\Booking;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function create(array $data): Booking;

    public function update(array $data, int $id): bool;

    public function delete(int $id): bool;

    public function all(): LengthAwarePaginator;

    public function find(int $id): Booking;

    public function findBy(string $columnName, $value): Booking;
}
