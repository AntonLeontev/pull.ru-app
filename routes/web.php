<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Src\Domain\CDEK\Services\FullfillmentApi;
use Src\Domain\MoySklad\Services\MoySkladApi;

Route::any('webhooks/insales/calculate_delivery', [DeliveryController::class, 'calculate']);
Route::post('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate']);
Route::post('webhooks/insales/orders_update', [InSalesController::class, 'ordersUpdate']);
Route::withoutMiddleware()
    ->post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate'])
    ->name('create');
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate'])->name('update');

if (app()->isLocal()) {
    Route::get('test', function () {
        // dd(MoySkladApi::getProduct('ca8aef1d-89e9-11ee-0a80-05a9004d0516')->json());
        // dd(MoySkladApi::getCharacteristics()->json());
        // dd(FullfillmentApi::getProducts()->json());

        $data = json_decode(file_get_contents(public_path('../tests/Fixtures/test_product_with_variants.json')), true);
        Http::timeout(1)->post(route('update'), $data);
    });
}
