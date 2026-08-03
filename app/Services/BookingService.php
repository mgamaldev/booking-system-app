<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use App\Strategies\BookingStrategies\BookingStrategyResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingService
{
    public function __construct(private BookingRepositoryInterface $bookingRepository) {}

    public function createBooking(array $data): Booking
    {
        $type = $data['type'] ?? 'one-on-one';

        return BookingStrategyResolver::resolve($type)->createBooking($data);
    }

    public function createBookingForCustomer(array $data, int $customerId): Booking
    {
        return $this->createBooking(array_merge($data, [
            'customer_id' => $customerId,
        ]));
    }

    public function updateBooking(array $data, int $id): bool
    {
        return $this->bookingRepository->update($data, $id);
    }

    public function updateExistingBooking(Booking $booking, array $data): Booking
    {
        $this->bookingRepository->update($data, $booking->id);

        return $this->bookingRepository->find($booking->id);
    }

    public function deleteBooking(int $id): bool
    {
        return $this->bookingRepository->delete($id);
    }

    public function getAllBookings(): LengthAwarePaginator
    {
        return $this->bookingRepository->all();
    }

    public function getBookingById(int $id): Booking
    {
        return $this->bookingRepository->find($id);
    }
}
