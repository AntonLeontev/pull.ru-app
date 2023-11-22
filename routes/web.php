<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use Illuminate\Support\Facades\Route;

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
Route::post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate']);
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate']);

if (app()->isLocal()) {
    Route::get('test', function () {

    });
}
