<?php

namespace App\Factories;

use InvalidArgumentException;

class BookingFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public static function resolve(string $type): BookingFactoryInterface
    {
        return match ($type) {
            'one-on-one' => new OneToOneBookingFactory,
            'group' => new GroupBookingFactory(),
            'recurring' => new RecurringBookingFactory,
            default => throw new InvalidArgumentException('Unsupported booking type'),
        };
    }
}
