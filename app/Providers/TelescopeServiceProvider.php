<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Src\Domain\Synchronizer\Models\Order;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            // if ($this->app->environment('local')) {
            //     return true;
            // }

            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });

        Telescope::tag(function (IncomingEntry $entry) {
            if ($entry->type === 'request') {
                if ($entry->content['uri'] === '/webhooks/cdek/order-status') {
                    $number = $this->getOrderNumber($entry);
                }

                return match ($entry->content['uri']) {
                    '/webhooks/insales/products_create' => ['webhook', 'insales', 'product create'],
                    '/webhooks/insales/products_update' => ['webhook', 'insales', 'product update'],
                    '/webhooks/insales/orders_create' => ['webhook', 'insales', 'orders create'],
                    '/webhooks/insales/orders_update' => ['webhook', 'insales', 'orders update'],
                    '/webhooks/moy_sklad/product_update' => ['webhook', 'ms', 'product update'],
                    '/webhooks/moy_sklad/variant_update' => ['webhook', 'ms', 'variant update'],
                    '/webhooks/cdek/order-status' => ['webhook', 'cdek', 'order status', 'order number:'.$number],
                    '/api/errors' => ['errors'],
                    default => [],
                };
            }

            return [];
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            //TODO change telescope access
            return true;
            // return in_array($user->email, [
            //     //
            // ]);
        });
    }

    private function getOrderNumber(IncomingEntry $entry): string
    {
        if (request()->json('attributes.is_return')) {
            $order = Order::where('fullfillment_id', request()->json('attributes.related_entities.cdek_number'))->first();

            return $order?->number ?? '';
        }

        return request()->json('attributes.number');
    }
}
