<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Services\CDEK\CdekApi;
use App\Services\CDEK\Entities\Address;
use App\Services\CDEK\Entities\Client;
use App\Services\CDEK\Entities\DeliveryRequest;
use App\Services\CDEK\Entities\OrderProduct;
use App\Services\CDEK\Entities\PaymentState;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('webhooks/delivery/calculate', [DeliveryController::class, 'calculate']);
Route::any('webhooks/delivery/widget', [DeliveryController::class, 'widget']);

Route::post('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate'])->name('order.create');
Route::post('webhooks/insales/orders_update', [InSalesController::class, 'ordersUpdate']);
Route::withoutMiddleware()
    ->post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate'])
    ->name('create');
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate'])->name('update');

if (app()->isLocal()) {
    Route::get('test', function () {
        // dd(MoySkladApi::getCharacteristics()->json());
        // dd(FullfillmentApi::pointByCode('YEKB300')->json('_embedded.servicePoints.0.id'));
        dd(FullfillmentApi::createOrder([
            'profile' => new Client('Anton', 'aner-anton@ya.ru'),
            'phone' => '89126510464',
            'shop' => config('services.cdekff.shop'),
            'extId' => null,
            'paymentState' => PaymentState::fromInsales('pending'),
            'orderProducts' => [
                OrderProduct::fromInsales(683239987, 2, 23000),
            ],
            'eav' => [
                'order-reserve-warehouse' => config('services.cdekff.warehouse'),
                'delivery-services-request' => true,
            ],
            'deliveryRequest' => DeliveryRequest::fromInsales(368, 245, 'MSK520'),
            'address' => new Address('Москва', 'Россия, Москва, Западный административный округ, район Раменки, территория Ленинские Горы, 1с73', '23'),
        ])->json());
        // dd(CdekApi::getToken()->json());
        // dd(InSalesApi::getWebhooks()->json());

        // $data = json_decode(file_get_contents(public_path('../tests/Fixtures/new_order.json')), true);
        // Http::timeout(1)->post(route('order.create'), $data);
    });
}
