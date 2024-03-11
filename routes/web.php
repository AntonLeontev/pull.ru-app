<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Http\Controllers\OnlinePaymentController;
use Illuminate\Support\Facades\Route;
use Src\Domain\Synchronizer\Actions\CreateOrderFromInsales;

Route::get('webhooks/delivery/calculate', [DeliveryController::class, 'calculate']);
Route::any('webhooks/delivery/widget', [DeliveryController::class, 'widget']);

Route::post('webhooks/online-payments/cloudpayments/pay', [OnlinePaymentController::class, 'cloudpaymentsPay']);

Route::post('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate'])->name('order.create');
Route::post('webhooks/insales/orders_update', [InSalesController::class, 'ordersUpdate']);
Route::withoutMiddleware()
    ->post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate'])
    ->name('create');
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate'])->name('update');
Route::post('webhooks/insales/external-payment', [InSalesController::class, 'externalPayment']);

Route::post('webhooks/moy_sklad/product_update', [MoySkladController::class, 'productUpdate']);
Route::post('webhooks/moy_sklad/variant_update', [MoySkladController::class, 'variantUpdate']);

Route::get('api/allowed_regions', [ApiController::class, 'allowedRegions']);
Route::get('api/organizations_brands', [ApiController::class, 'organizationsAndBrands']);

if (app()->isLocal()) {
    Route::get('test', function (CreateOrderFromInsales $action) {
        dd(json_decode('{"deliveryPrice":610,"deliveryTariff":{"tariff_code":483,"tariff_name":"Экспресс склад-склад","tariff_description":"Экспресс-доставка","delivery_mode":4,"delivery_sum":610,"period_min":1,"period_max":2,"calendar_min":1,"calendar_max":2},"deliveryType":"office","deliveryAddress":{"city_code":13104,"city":"Новоивановское","type":"PVZ","postal_code":"143026","country_code":"RU","region":"Московская область","have_cashless":true,"have_cash":true,"allowed_cod":true,"is_dressing_room":true,"code":"NOV31","name":"На Эйнштейна 4","address":"б-р Эйнштейна, 4","work_time":"Пн-Вс 10:00-20:00","location":[37.364697,55.702084],"weight_min":0,"weight_max":70000,"dimensions":null}}', false));
    });
}
