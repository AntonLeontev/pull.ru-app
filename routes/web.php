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
use Illuminate\Support\Facades\Route;
use Src\Domain\Synchronizer\Actions\CreateOrderFromInsales;

Route::get('webhooks/delivery/calculate', [DeliveryController::class, 'calculate']);
Route::any('webhooks/delivery/widget', [DeliveryController::class, 'widget']);

Route::any('webhooks/online-payments/tinkoff', [OnlinePaymentController::class, 'tinkoff']);
Route::any('webhooks/online-payments/tinkoff-success', [OnlinePaymentController::class, 'tinkoffSuccess']);
Route::any('webhooks/online-payments/tinkoff-fail', [OnlinePaymentController::class, 'tinkoffFail']);

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

if (app()->isLocal()) {
    Route::get('test', function (CreateOrderFromInsales $action) {

        // dump(MoySkladApi::getVariant('0e0e1b5c-c733-11ee-0a80-0e1e001ec893')->json());
        // dd(FullfillmentApi::updateSimpleProduct(32233198, ['barcodes' => ['2000000003191']])->json());
        // $regs = collect(CdekApi::regions()->json());

        // dd(InSalesApi::updateVariant(415374444, 695320116, [
        // 	'old_price' => 12500,
        // ])->json());
        // dd(CdekApi::getOrder('72753034-0747-4bc0-a2b5-725dd95359bb')->json());
        // foreach ($resp as $product) {
        // 	if ($product['title'] === 'Джинсы Burberry') {
        // 		$result[] = $product;
        // 	}
        // }
        // dump($result);

    });
}
