<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

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
            if ($this->app->environment('local')) {
                return true;
            }

            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });

        Telescope::tag(function (IncomingEntry $entry) {
            if ($entry->type === 'request') {
                return match ($entry->content['uri']) {
                    '/webhooks/insales/products_create' => ['insales', 'product create'],
                    '/webhooks/insales/products_update' => ['insales', 'product update'],
                    '/webhooks/insales/orders_create' => ['insales', 'orders update'],
                    '/webhooks/insales/orders_update' => ['insales', 'orders update'],
                    '/webhooks/moy_sklad/product_update' => ['ms', 'product update'],
                    '/webhooks/moy_sklad/variant_update' => ['ms', 'variant update'],
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
}
