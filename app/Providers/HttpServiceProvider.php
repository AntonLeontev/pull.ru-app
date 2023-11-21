<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Http::macro('cdek', function () {
            return Http::baseUrl('https://cdek.orderadmin.ru')
                ->acceptJson()
                ->withBasicAuth(config('services.cdek.login'), config('services.cdek.password'));
        });

        Http::macro('inSales', function () {
            return Http::acceptJson()
                ->withBasicAuth(config('services.inSales.login'), config('services.inSales.password'))
                ->asJson()
                ->baseUrl('https://myshop-cdw517.myinsales.ru');
        });

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
