<?php

namespace App\Factories;

// use App\Exceptions\DomainException;
use _PHPStan_a8704accb\Nette\Neon\Exception;

class BookingFactory
{
    /**
     * @throws Exception
     */
    public static function resolve(string $type): BookingFactoryInterface
    {
        return match ($type) {
            'one-on-one' => new OneToOneBookingFactory,
            'group' => new GroupBookingFactory,
            'recurring' => new RecurringBookingFactory,
            default => throw new Exception('Unsupported booking type'),
        };
    }
}
