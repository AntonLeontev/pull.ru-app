<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderNotDelivered;
use App\Listeners\GetLocationByIp;
use App\Listeners\SendOrderCreatedTelegramNotification;
use App\Listeners\SendOrderDeliveredTelegramNotification;
use App\Listeners\SendOrderPartlyDeliveredTelegramNotification;
use App\Listeners\SentOrderNotDeliveredTelegramNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Src\Domain\DiscountSystem\Listeners\ScheduleDiscountUpdatingFromDeliveredOrder;
use Src\Domain\Synchronizer\Events\OrderAcceptedAtPickPoint;
use Src\Domain\Synchronizer\Events\OrderDelivered;
use Src\Domain\Synchronizer\Events\OrderPartlyDelivered;
use Src\Domain\Synchronizer\Events\OrderTakenByCourier;
use Src\Domain\Synchronizer\Events\ProductCreatingError;
use Src\Domain\Synchronizer\Events\ProductCreatingSuccess;
use Src\Domain\Synchronizer\Events\ProductUpdatingError;
use Src\Domain\Synchronizer\Events\ProductUpdatingSuccess;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToCdekError;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToCdekSuccess;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesError;
use Src\Domain\Synchronizer\Events\VariantFromMoySkladToInsalesSuccess;
use Src\Domain\Synchronizer\Listeners\CreateDemandInMS;
use Src\Domain\Synchronizer\Listeners\CreateTelegramLog;
use Src\Domain\Synchronizer\Listeners\SendReceipt;
use Src\Domain\Synchronizer\Listeners\SetCourierOrderStatus;
use Src\Domain\Synchronizer\Listeners\SetKeepFreeDateToInsales;
use Src\Domain\Synchronizer\Listeners\SetPickPointOrderStatus;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ProductCreatingSuccess::class => [CreateTelegramLog::class],
        ProductUpdatingSuccess::class => [CreateTelegramLog::class],
        ProductCreatingError::class => [CreateTelegramLog::class],
        ProductUpdatingError::class => [CreateTelegramLog::class],
        VariantFromMoySkladToCdekSuccess::class => [CreateTelegramLog::class],
        VariantFromMoySkladToCdekError::class => [CreateTelegramLog::class],
        VariantFromMoySkladToInsalesSuccess::class => [CreateTelegramLog::class],
        VariantFromMoySkladToInsalesError::class => [CreateTelegramLog::class],

        OrderCreated::class => [
            SendOrderCreatedTelegramNotification::class,
            GetLocationByIp::class,
        ],

        OrderTakenByCourier::class => [
            SetKeepFreeDateToInsales::class,
            SetCourierOrderStatus::class,
        ],
        OrderAcceptedAtPickPoint::class => [
            SetKeepFreeDateToInsales::class,
            SetPickPointOrderStatus::class,
        ],
        OrderPartlyDelivered::class => [
            SendReceipt::class,
            CreateDemandInMS::class,
            SendOrderPartlyDeliveredTelegramNotification::class,
        ],
        OrderDelivered::class => [
            SendReceipt::class,
            CreateDemandInMS::class,
            SendOrderDeliveredTelegramNotification::class,
            ScheduleDiscountUpdatingFromDeliveredOrder::class,
        ],

        OrderNotDelivered::class => [
            SentOrderNotDeliveredTelegramNotification::class,
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
