<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InSalesController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Src\Domain\InSales\Services\InSalesApi;
use Src\Domain\MoySklad\Services\MoySkladApi;

Route::any('webhooks/insales/calculate_delivery', [DeliveryController::class, 'calculate']);
Route::any('webhooks/insales/orders_create', [InSalesController::class, 'ordersCreate']);
Route::withoutMiddleware()
    ->post('webhooks/insales/products_create', [InSalesController::class, 'productsCreate'])->name('create');
Route::post('webhooks/insales/products_update', [InSalesController::class, 'productsUpdate'])->name('update');

if (app()->isLocal()) {
    Route::get('test', function () {
        // dd(MoySkladApi::getProduct('ca8aef1d-89e9-11ee-0a80-05a9004d0516')->json());
        dd(MoySkladApi::getCharacteristics()->json());
        dd(InSalesApi::getOptionNames()->json());
        Http::timeout(1)
            ->post(route('update'), [
                [
                    'id' => 408696716,
                    'category_id' => 32380146,
                    'created_at' => '2023-11-22T14:24:30.000+03:00',
                    'updated_at' => '2023-11-22T14:24:30.000+03:00',
                    'is_hidden' => false,
                    'available' => true,
                    'archived' => false,
                    'canonical_url_collection_id' => 27837817,
                    'custom_template' => null,
                    'unit' => 'pce',
                    'sort_weight' => null,
                    'ignore_discounts' => null,
                    'vat' => -1,
                    'dimensions' => null,
                    'fiscal_product_type' => 1,
                    'title' => 'TEST',
                    'short_description' => null,
                    'permalink' => 'esche-tovar',
                    'html_title' => null,
                    'meta_keywords' => null,
                    'meta_description' => null,
                    'currency_code' => 'RUR',
                    'collections_ids' => [
                        27837968,
                    ],
                    'sales_channels_id' => null,
                    'description' => null,
                    'images' => [],
                    'video_links' => [],
                    'option_names' => [],
                    'properties' => [],
                    'characteristics' => [],
                    'product_field_values' => [],
                    'variants' => [
                        [
                            'id' => 680650527,
                            'title' => null,
                            'product_id' => 408696716,
                            'sku' => 'testArticle',
                            'barcode' => null,
                            'dimensions' => null,
                            'available' => true,
                            'image_ids' => [],
                            'image_id' => null,
                            'weight' => '0.6',
                            'created_at' => '2023-11-22T14:24:30.000+03:00',
                            'updated_at' => '2023-11-22T14:24:30.000+03:00',
                            'quantity' => null,
                            'quantity_at_warehouse0' => 0,
                            'cost_price' => '12.1',
                            'cost_price_in_site_currency' => null,
                            'price_in_site_currency' => '0.0',
                            'base_price' => '0.0',
                            'old_price' => null,
                            'price2' => null,
                            'price3' => null,
                            'price' => '200.1',
                            'base_price_in_site_currency' => '0.0',
                            'old_price_in_site_currency' => null,
                            'price2_in_site_currency' => null,
                            'price3_in_site_currency' => null,
                            'prices' => [
                                null,
                                null,
                            ],
                            'prices_in_site_currency' => [
                                null,
                                null,
                            ],
                            'variant_field_values' => [],
                            'option_values' => [],
                        ],
                    ],
                    'product_bundle_components' => [],
                ],
            ]);

    });
}
