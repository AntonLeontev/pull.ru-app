<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Http\Controllers\OnlinePaymentController;
use App\Services\CDEK\CdekApi;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\MoySkladApi;
use App\Services\Tinkoff\Entities\ReceiptItem;
use App\Services\Tinkoff\Enums\PaymentObject;
use App\Services\Tinkoff\TinkoffApi;
use Illuminate\Support\Facades\Route;
use Src\Domain\Synchronizer\Actions\CreateOrderFromInsales;

Route::get('webhooks/delivery/calculate', [DeliveryController::class, 'calculate']);
Route::any('webhooks/delivery/widget', [DeliveryController::class, 'widget']);

Route::post('webhooks/online-payments', [OnlinePaymentController::class, 'tinkoff']);

Route::post('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate'])->name('order.create');
Route::post('webhooks/insales/orders_update', [InSalesController::class, 'ordersUpdate']);
Route::withoutMiddleware()
    ->post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate'])
    ->name('create');
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate'])->name('update');

Route::post('webhooks/moy_sklad/product_update', [MoySkladController::class, 'productUpdate']);
Route::post('webhooks/moy_sklad/variant_update', [MoySkladController::class, 'variantUpdate']);

Route::get('api/allowed_regions', [ApiController::class, 'allowedRegions']);

if (app()->isLocal()) {
    Route::get('test', function (CreateOrderFromInsales $action) {
        // dump(MoySkladApi::getProduct('8d951a73-b3a0-11ee-0a80-09580088ec5f')->json());
        // dd(FullfillmentApi::updateSimpleProduct(32233198, ['barcodes' => ['2000000003191']])->json());
        // $regs = collect(CdekApi::regions()->json());

        // $t = CdekApi::deliverypoints(['region_code' => 67]);
        // $resp = InSalesApi::getProducts(perPage: 200)->json();
        // $result = [];

        $items = [new ReceiptItem('Test', 120000, 2, 240000, PaymentObject::commodity)];

        dd(TinkoffApi::init(240000, 3, 'aner-anton@ya.ru', $items)->json());
        // foreach ($resp as $product) {
        // 	if ($product['title'] === 'Джинсы Burberry') {
        // 		$result[] = $product;
        // 	}
        // }
        // dump($result);

    });
}
