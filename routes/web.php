<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use App\Http\Controllers\MoySkladController;
use App\Services\CDEK\CdekApi;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use App\Services\MoySklad\MoySkladApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Src\Domain\Synchronizer\Actions\CreateOrderFromInsales;

Route::get('webhooks/delivery/calculate', [DeliveryController::class, 'calculate']);
Route::any('webhooks/delivery/widget', [DeliveryController::class, 'widget']);

Route::post('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate'])->name('order.create');
Route::post('webhooks/insales/orders_update', [InSalesController::class, 'ordersUpdate']);
Route::withoutMiddleware()
    ->post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate'])
    ->name('create');
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate'])->name('update');

Route::post('webhooks/moy_sklad/product_update', [MoySkladController::class, 'productUpdate']);
Route::post('webhooks/moy_sklad/variant_update', [MoySkladController::class, 'variantUpdate']);

if (app()->isLocal()) {
    Route::get('test', function (CreateOrderFromInsales $action) {
        // dd(MoySkladApi::getVariant('01f2fd01-9d05-11ee-0a80-136b004daad4')->json());
        // dd(FullfillmentApi::updateSimpleProduct(32233198, ['barcodes' => ['2000000003191']])->json());
        // $regs = collect(CdekApi::regions()->json());

        // dd($regs->filter(fn ($value) => str_contains($value['region'], 'лта')));
        // dd(CdekApi::deliverypoints(['region_code' => 41])->json());
        // $products = InSalesApi::getVariants(412972193)->json();

        $data = json_decode(file_get_contents(public_path('../tests/Fixtures/new_order.json')), true);
        $action->handle($data);

        // dd($data, data_get($data, 'events.0.updatedFields'));
        // $data['name'] = (string) random_int(1, 999);
        // dd(Http::moySklad()->post('entity/customerorder', $data)->json());
    });
}
