<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/delivery/locality', [DeliveryController::class, 'locality']);
Route::get('webhooks/delivery/calculate', [DeliveryController::class, 'calculate']);

Route::post('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate'])->name('order.create');
Route::post('webhooks/insales/orders_update', [InSalesController::class, 'ordersUpdate']);
Route::withoutMiddleware()
    ->post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate'])
    ->name('create');
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate'])->name('update');

if (app()->isLocal()) {
    Route::get('test', function () {
        // dd(MoySkladApi::getCharacteristics()->json());
        // dd(FullfillmentApi::getLocalities('Москва')->json());
        // dd(InSalesApi::getOptionNames()->json());

        $data = json_decode(file_get_contents(public_path('../tests/Fixtures/new_order.json')), true);
        Http::timeout(1)->post(route('order.create'), $data);
    });
}
