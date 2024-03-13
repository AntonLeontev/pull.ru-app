<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Http\Controllers\OnlinePaymentController;
use App\Services\CDEK\FullfillmentApi;
use App\Services\Cloudpayments\CloudPaymentsService;
use App\Services\Cloudpayments\Entities\Amounts;
use App\Services\Cloudpayments\Entities\CustomerReceipt;
use App\Services\Cloudpayments\Entities\Item;
use App\Services\Cloudpayments\Entities\PurveyorData;
use App\Services\Cloudpayments\Enums\AgentSign;
use App\Services\Cloudpayments\Enums\TaxationSystem;
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
        // dd(FullfillmentApi::getOrderByExtId(1530719072)->json());

        $items = [];

        $items[] = new Item(
            'Test product',
            1200.00,
            1,
            0,
            1200,
            AgentSign::agent,
            new PurveyorData('ИП Поставщик', '450205667694')
        );

        $amounts = new Amounts(1200);

        $customerReceipt = new CustomerReceipt(
            $items,
            $amounts,
            TaxationSystem::usn_income,
            'aner-anton@yandex.ru',
        );
        $invoiceId = random_int(900, 999);

        dd(app(CloudPaymentsService::class)->receipt($customerReceipt, $invoiceId));
    });
}
