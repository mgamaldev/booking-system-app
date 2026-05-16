<?php

namespace App\Strategies\BookingStrategies;

class BookingStrategyResolver
{
    public static function resolve(string $type): BookingStrategyInterface {
        return match ($type) {
            'one-on-one' => new OneToOneBookingStrategy,
            'group'      => new GroupBookingStrategy,
            'recurring'  => new RecurringBookingStrategy,
            default => throw new \InvalidArgumentException('Unsupported booking type'),
        };
    }
}
