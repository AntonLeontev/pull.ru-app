<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Http\Controllers\OnlinePaymentController;
use App\Services\CDEK\CdekApi;
use Illuminate\Support\Facades\Route;
use Src\Domain\FinancialAccounting\Actions\CreateOperationsFromOrder;
use Src\Domain\FinancialAccounting\DTO\CdekOrderDTO;
use Src\Domain\Synchronizer\Models\Order;

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
Route::middleware('throttle:60,1')->post('api/rightholders', [ApiController::class, 'rightholders']);
Route::middleware('throttle:60,1')
    ->post('api/errors', function () {
        return response()->json(['ok' => true, 'request' => request()->all()]);
    });

if (app()->isLocal()) {
    Route::get('test', function (CreateOperationsFromOrder $action) {
        // $response = CdekApi::getOrderByCdekId(1529445146);

        // if ($response->json('entity.sender.company') === 'Фулфилмент' && is_null($response->json('entity.number'))) {
        // 	return;
        // }

        // $orderDto = CdekOrderDTO::fromResponse($response);

        // if ($orderDto->isReturn) {
        // 	$order = Order::where('cdek_id', $orderDto->directOrderUuid)->first();

        // 	$orderDto->number = $order?->number;
        // }

        // dd($orderDto);
        // $action->handle($orderDto);

        // dump(
        //     // CdekApi::getOrder()->json()
        // );
    });
}
