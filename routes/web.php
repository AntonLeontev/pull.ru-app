<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CdekExpendsImportController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Http\Controllers\OnlinePaymentController;
use App\Services\Dicards\DicardsService;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Support\Facades\Route;

Route::get('webhooks/delivery/calculate', [DeliveryController::class, 'calculate']);
Route::any('webhooks/delivery/widget', [DeliveryController::class, 'widget']);
Route::any('webhooks/cdek/order-status', [DeliveryController::class, 'orderStatus']);

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
Route::get('api/additition-data', [ApiController::class, 'addititionData']);
Route::middleware('throttle:10,60')->post('api/footer-subscribe', [ApiController::class, 'footerSubscribe']);
Route::middleware('throttle:60,1')->post('api/rightholders', [ApiController::class, 'rightholders']);

Route::get('/keep-alive', function () {
    return response()->json(['ok' => true]);
});

if (config('app.url') === 'http://localhost:8000') {
    Route::get('test', function (DicardsService $service) {
        // $c = MoySkladApi::getCounterparties()->collect('rows')->first();
        // dd($c);

    });
}

Route::prefix(config('moonshine.route.prefix', ''))
    ->as('moonshine.')
    ->group(static function () {
        Route::controller(AdminAuthController::class)
            ->middleware('throttle:3,5')
            ->group(static function (): void {
                Route::post('/authenticate', 'authenticateFirstFactor')->name('authenticate');
                Route::post('/authenticate2f', 'authenticateSecondFactor')->name('authenticate2f');
            });

        Route::post('cdek-expends-import/create', [CdekExpendsImportController::class, 'create'])->name('cdek-expends-import.create');
    });
