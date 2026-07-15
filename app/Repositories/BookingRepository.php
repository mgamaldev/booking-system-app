<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Interfaces\BookingCancellationRepositoryInterface;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingRepository implements BookingCancellationRepositoryInterface, BookingRepositoryInterface
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

    /**
     * @throws ModelNotFoundException
     */
    public function findBy(string $columnName, mixed $value): Booking
    {
        /** @var Booking $booking */
        $booking = Booking::query()
            ->where($columnName, $value)
            ->firstOrFail();

        return $booking;
    }

    public function getBookingForReminder(int $daysBeforeReminder): Collection
    {
        $reminderDate = Carbon::now()->addDays($daysBeforeReminder)->toDateString();

        return Booking::query()
            ->confirmed()
            ->whereHas('slot', function ($query) use ($reminderDate) {
                $query->whereDate('date', $reminderDate);
            })
            ->with(['customer', 'slot'])
            ->get();
    }

    public function claimBookingReminders(int $daysBeforeReminder): Collection
    {
        $reminderDate = Carbon::now()->addDays($daysBeforeReminder)->toDateString();
        $now = Carbon::now();

        return DB::transaction(function () use ($reminderDate, $now) {
            $bookings = Booking::query()
                ->confirmed()
                ->whereNull('reminder_sent_at')
                ->whereHas('slot', function ($query) use ($reminderDate) {
                    $query->whereDate('date', $reminderDate);
                })
                ->lockForUpdate()
                ->with(['customer', 'slot'])
                ->get();

            if ($bookings->isEmpty()) {
                return $bookings;
            }

            Booking::query()
                ->whereIn('id', $bookings->pluck('id'))
                ->update(['reminder_sent_at' => $now]);

            return $bookings->each(function (Booking $booking) use ($now) {
                $booking->reminder_sent_at = $now;
            });
        });
    }

    public function findForCancellation(int $bookingId): Booking
    {
        /** @var Booking $booking */
        $booking = Booking::query()
            ->with('slot')
            ->findOrFail($bookingId);

        return $booking;
    }

    public function cancel(Booking $booking): Booking
    {
        $booking->update([
            'status' => 'canceled',
        ]);

        return $booking->refresh();
    }
}
