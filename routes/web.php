<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CdekExpendsImportController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\RegisterClientController;
use App\Http\Controllers\SubscribtionsController;
use App\Services\Unisender\UnisenderService;
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
Route::post('webhooks/insales/client_create', [InSalesController::class, 'clientCreate']);
Route::post('webhooks/insales/get_discount', [InSalesController::class, 'getDiscount']);

Route::post('webhooks/moy_sklad/product_update', [MoySkladController::class, 'productUpdate']);
Route::post('webhooks/moy_sklad/variant_update', [MoySkladController::class, 'variantUpdate']);
Route::post('webhooks/moy_sklad/counterparty_create', [MoySkladController::class, 'counterpartyCreate']);
Route::post('webhooks/moy_sklad/retaildemand_create', [MoySkladController::class, 'retaildemandCreate']);
Route::post('webhooks/moy_sklad/retailsalesreturn_create', [MoySkladController::class, 'retailsalesreturnCreate']);

Route::get('api/allowed_regions', [ApiController::class, 'allowedRegions']);
Route::get('api/organizations_brands', [ApiController::class, 'organizationsAndBrands']);
Route::get('api/additition-data', [ApiController::class, 'addititionData']);
Route::middleware('throttle:60,1')->post('api/rightholders', [ApiController::class, 'rightholders']);

Route::controller(SubscribtionsController::class)->group(function () {
    Route::middleware('throttle:10,60')->post('api/footer-subscribe', 'subscribeFromFooterForm');
    Route::middleware('throttle:10,60')->post('api/stylist-subscribe', 'subscribeStylistConsultation');
});

Route::get('/keep-alive', function () {
    return response()->json(['ok' => true]);
});

Route::controller(RegisterClientController::class)->group(function () {
    Route::get('register', 'show');
    Route::post('register', 'create')->middleware(['precognitive', 'throttle:50,1']);
    Route::get('register_for_cashier', 'showForCashier');
    Route::post('register_for_cashier', 'createForCashier')->middleware(['precognitive', 'throttle:50,1']);
    Route::post('api/register-from-main', 'registerFromMain');
});

if (config('app.url') === 'http://localhost:8000') {
    Route::get('test', function (UnisenderService $service) {
        $c = $service->api->getFields()->json();
        dd($c);
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
