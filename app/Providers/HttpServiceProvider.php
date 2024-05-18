<?php

namespace App\Providers;

use App\Services\CDEK\Exceptions\CdekApiException;
use App\Services\CDEK\Exceptions\FullfillmentApiException;
use App\Services\Cloudpayments\Exceptions\CloudPaymentsApiException;
use App\Services\InSales\Exceptions\InsalesRateLimitException;
use App\Services\MoySklad\Exceptions\MoySkladApiException;
use App\Services\Planfact\Exceptions\PlanfactBadRequestException;
use App\Services\Tinkoff\Exceptions\TinkoffApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Http::macro('cdekff', function () {
            return Http::baseUrl('https://cdek.orderadmin.ru/api')
                ->retry(3, 1000)
                ->acceptJson()
                ->withBasicAuth(config('services.cdekff.login'), config('services.cdekff.password'))
                ->throw(function (Response $response) {
                    throw new FullfillmentApiException($response);
                });
        });

        Http::macro('cdek', function () {
            return Http::baseUrl(config('services.cdek.base_url'))
                ->retry(3, 1000)
                ->acceptJson()
                ->withHeader('Authorization', 'Bearer '.cdek_auth_token())
                ->throw(function (Response $response) {
                    if ($response->json('errors.0.message') === 'Recipient location is not recognized') {
                        abort($response->status(), $response->json('errors.0.message'));
                    }

                    if ($response->json('errors.0.message') === 'Invalid value type in [to_location] field') {
                        abort($response->status(), $response->json('errors.0.message'));
                    }

                    throw new CdekApiException($response);
                });
        });

        Http::macro('inSales', function () {
            return Http::acceptJson()
                ->retry(3, 1000)
                ->withBasicAuth(config('services.inSales.login'), config('services.inSales.password'))
                ->asJson()
                ->baseUrl('https://myshop-cdw517.myinsales.ru')
                ->throw(function (Response $response) {
                    if ($response->status() === 429) {
                        throw new InsalesRateLimitException();
                    }
                });
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

        Http::macro('tinkoff', function () {
            return Http::asJson()
                ->retry(2, 1000)
                ->acceptJson()
                ->baseUrl('https://securepay.tinkoff.ru/v2/')
                ->throw(function (Response $response) {
                    throw new TinkoffApiException($response);
                });
        });

        Http::macro('sm-register', function () {
            return Http::asJson()
                ->retry(2, 1000)
                ->baseUrl('https://sm-register.tinkoff.ru');
        });

        Http::macro('cloudpayments', function () {
            return Http::baseUrl('https://api.cloudpayments.ru')
                ->asJson()
                ->timeout(300)
                ->connectTimeout(15)
                ->withBasicAuth(config('services.cloudpayments.public_id'), config('services.cloudpayments.password'))
                ->throw(function (Response $response) {
                    throw new CloudPaymentsApiException($response);
                });

        });

        Http::macro('planfact', function () {
            return Http::withHeaders(['X-ApiKey' => config('services.planfact.api_key')])
                ->when(! app()->isProduction(), function ($request) {
                    return $request->withOptions(['verify' => false]);
                })
                ->timeout(8)
                ->asJson()
                ->acceptJson()
                ->baseUrl('https://api.planfact.io')
                ->throw(function (Response $response) {
                    throw new PlanfactBadRequestException($response);
                });
        });
    }
}
