<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Src\Domain\Synchronizer\Events\ProductCreatingError;
use Src\Domain\Synchronizer\Events\ProductCreatingSuccess;
use Src\Domain\Synchronizer\Events\ProductUpdatingError;
use Src\Domain\Synchronizer\Events\ProductUpdatingSuccess;
use Src\Domain\Synchronizer\Listeners\LogToTelegram;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ProductCreatingSuccess::class => [
            LogToTelegram::class,
        ],
        ProductUpdatingSuccess::class => [
            LogToTelegram::class,
        ],
        ProductCreatingError::class => [
            LogToTelegram::class,
        ],
        ProductUpdatingError::class => [
            LogToTelegram::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
