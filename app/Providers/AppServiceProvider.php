<?php

namespace App\Providers;

use App\Repositories\BookingRepository;
use App\Repositories\Interfaces\BookingRepositoryInterface;
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
        App::bind(BookingRepositoryInterface::class, BookingRepository::class);
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
