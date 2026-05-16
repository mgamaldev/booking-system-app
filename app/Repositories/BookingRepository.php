<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data): Booking|Model
    {
        return Booking::create($data);
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

    public function find(int $id): Booking|Model
    {
        return Booking::query()->findOrFail($id);
    }

    public function findBy(string $columnName, $value): Booking|Model
    {
        return Booking::query()->where($columnName, $value)->firstOrFail();
    }
}
