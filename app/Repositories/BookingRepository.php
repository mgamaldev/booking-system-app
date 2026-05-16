<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data): Booking
    {
        /** @var Booking $booking */
        $booking = Booking::query()->create($data);

        return $booking;
    }

    public function update(array $data, int $id): bool
    {
        return $this->find($id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }

    public function all(): LengthAwarePaginator
    {
        return Booking::query()->paginate();
    }

    public function find(int $id): Booking
    {
        /** @var Booking|null $booking */
        $booking = Booking::query()->findOrFail($id);

        return $booking;
    }

    public function findBy(string $columnName, $value): Booking
    {
        /** @var Booking|null $booking */
        $booking = Booking::query()->where($columnName, $value)->first();

        return $booking;
    }
}
