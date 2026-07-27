<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BookingService
{
    public function __construct(private BookingRepositoryInterface $bookingRepository) {}

    public function createBooking(array $data): Booking
    {

        return $this->bookingRepository->create($data);
    }

    public function updateBooking(array $data, int $id): bool
    {
        return $this->bookingRepository->update($data, $id);
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

    public function getBookingForReminder(int $daysBeforeReminder): Collection
    {
        return $this->bookingRepository->getBookingForReminder($daysBeforeReminder);
    }

    public function claimBookingReminders(int $daysBeforeReminder): Collection
    {
        return $this->bookingRepository->claimBookingReminders($daysBeforeReminder);
    }

    public function markReminderAsSent(Booking $booking): bool
    {
        return $this->bookingRepository->markReminderAsSent($booking);
    }
}
