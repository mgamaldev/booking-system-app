<?php




namespace App\Factories;

//use App\Exceptions\DomainException;
use _PHPStan_a8704accb\Nette\Neon\Exception;
use App\Models\Booking;

class BookingFactory
{

    /**
     * @throws Exception
     */
    public static function  resolve(string $type): BookingFactoryInterface
    {
        return match ($type) {
            'one-on-one' =>  new OneToOneBookingFactory(),
            'group' =>   new GroupBookingFactory(),
            'recurring' => New RecurringBookingFactory(),
            default => throw new Exception("Unsupported booking type"),
        };
    }
}
