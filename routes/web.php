<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use Illuminate\Support\Facades\Route;
use Src\Domain\InSales\Services\InSalesApi;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::any('webhooks/insales/calculate_delivery', [DeliveryController::class, 'calculate']);
Route::any('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate']);
Route::any('webhooks/insales/products_create', [InSalesController::class, 'productsCreate']);

if (app()->isProduction()) {
    Route::get('test', function () {
        // dd(FullfillmentApi::getLocalities()->json());
        // InSalesApi::createWebhook('http://pull.anerank4.beget.tech/webhooks/insales/products_create', 'products/create');
        dd(InSalesApi::getWebhooks()->json());
    });
}
