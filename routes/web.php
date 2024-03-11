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
        dd(json_decode('{"deliveryPrice":580,"deliveryTariff":{"tariff_code":482,"tariff_name":"Экспресс склад-дверь","tariff_description":"Экспресс-доставка","delivery_mode":3,"delivery_sum":580,"period_min":1,"period_max":2,"calendar_min":1,"calendar_max":2},"deliveryType":"door","deliveryAddress":{"city":"посёлок Отрадное","position":[37.32845,55.872102],"bounds":{"lower":[37.324344,55.869794],"upper":[37.332555,55.87441]},"name":"Лесная улица, 16","kind":"house","precision":"exact","formatted":"Россия, Московская область, городской округ Красногорск, посёлок Отрадное, Лесная улица, 16","country_code":"RU","postal_code":"143442","components":[{"kind":"country","name":"Россия"},{"kind":"province","name":"Центральный федеральный округ"},{"kind":"province","name":"Московская область"},{"kind":"area","name":"городской округ Красногорск"},{"kind":"locality","name":"посёлок Отрадное"},{"kind":"street","name":"Лесная улица"},{"kind":"house","name":"16"}]}}', false));
    });
}
