<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Services\CDEK\CdekApi;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\Enums\WebhookAction;
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

Route::post('webhooks/moy_sklad/product_update', [MoySkladController::class, 'productUpdate']);

if (app()->isLocal()) {
    Route::get('test', function () {
        // dd(MoySkladApi::createWebhook(
        // 	'https://api.pull.ru/webhooks/moy_sklad/product_update',
        // 	WebhookAction::update,
        // 	'product',
        // 	'FIELDS'
        // )->json());
        // dd(FullfillmentApi::pointByCode('YEKB300')->json('_embedded.servicePoints.0.id'));
        // dd(CdekApi::getToken()->json());
        // dd(InSalesApi::getWebhooks()->json());

        // $data = json_decode(file_get_contents(public_path('../tests/Fixtures/new_order.json')), true);
        // Http::timeout(1)->post(route('order.create'), $data);
    });
}
