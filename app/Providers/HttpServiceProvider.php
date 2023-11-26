<?php

namespace App\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Src\Domain\MoySklad\Exceptions\MoySkladApiException;

class HttpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Http::macro('cdek', function () {
            return Http::baseUrl('https://cdek.orderadmin.ru/api')
                ->retry(3, 1000)
                ->acceptJson()
                ->withBasicAuth(config('services.cdek.login'), config('services.cdek.password'));
        });

        Http::macro('inSales', function () {
            return Http::acceptJson()
                ->retry(3, 1000)
                ->withBasicAuth(config('services.inSales.login'), config('services.inSales.password'))
                ->asJson()
                ->baseUrl('https://myshop-cdw517.myinsales.ru');
        });

        Http::macro('moySklad', function () {
            return Http::asJson()
                ->retry(3, 1000)
                ->accept('application/json;charset=utf-8')
                ->withHeader('Authorization', 'Bearer '.config('services.moySklad.token'))
                ->withHeader('Accept-Encoding', 'gzip')
                ->baseUrl('https://api.moysklad.ru/api/remap/1.2')
                ->throw(function (Response $response) {
                    throw new MoySkladApiException($response);
                });
        });
    }
}
