<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use Illuminate\Support\Facades\Route;

Route::any('webhooks/insales/calculate_delivery', [DeliveryController::class, 'calculate']);
Route::any('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate']);
Route::post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate']);
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate']);

if (app()->isLocal()) {
    Route::get('test', function () {

    });
}
