<?php

namespace App\Providers;

use App\Events\BookingConfirmed;
use App\Listeners\BookingConfirmationNotificationListener;
use App\Listeners\LogConfirmedBooking;
use App\Repositories\BookingRepositoryInterface;
use App\Repositories\NullBookingRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @throws \ReflectionException
     */
    public function register(): void
    {
        App::bind(BookingRepositoryInterface::class, NullBookingRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BookingConfirmed::class, [BookingConfirmationNotificationListener::class, 'handle']);
        Event::listen(BookingConfirmed::class, [LogConfirmedBooking::class, 'handle']);

    }
}
