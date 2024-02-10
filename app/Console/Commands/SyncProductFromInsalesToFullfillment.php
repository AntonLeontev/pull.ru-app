<?php

namespace App\Console\Commands;

use App\Services\CDEK\Entities\Dimensions;
use App\Services\CDEK\Entities\Weight;
use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Models\Variant;

class SyncProductFromInsalesToFullfillment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-product-is-ff {--id=} {--ISid=}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('id')) {
            $this->syncById();

            return;
        }

        if ($this->option('ISid')) {
            $this->syncByInsalesId();

            return;
        }

        $this->error('Нужно указать --id или --ISid');
    }

    private function syncById(): void
    {
        $dbProduct = Product::find($this->option('id'));

        if (is_null($dbProduct)) {
            $this->error('Товар не найден');
        }

        $ISProduct = InSalesApi::getProduct($dbProduct->insales_id)->json();

        if ($ISProduct['archived']) {
            $this->error('Товар архивный в инсейлс');
        }

        $this->info("Синхронизация $dbProduct->name. id $dbProduct->id. IS id $dbProduct->insales_id");

        $hasVariants = count(data_get($ISProduct, 'variants')) > 1;

        $variants = [];

        foreach (data_get($ISProduct, 'variants') as $variant) {
            $dbVariant = Variant::updateOrCreate(
                ['insales_id' => $variant['id']],
                [
                    'name' => $variant['title'] ?? data_get($ISProduct, 'title'),
                    'product_id' => $dbProduct->id,
                ]
            );

            $name = $hasVariants ? $dbProduct->name.' '.$dbVariant->name : $dbProduct->name;

            if ($variant['image_id']) {
                try {
                    $image = InSalesApi::getImage($ISProduct['id'], $variant['image_id'])->json('large_url');
                } catch (\Throwable $th) {
                    $image = data_get($ISProduct, 'images.0.large_url');
                }
            }

            if (is_null($dbVariant->cdek_id)) {
                $cdekProduct = FullfillmentApi::createSimpleProduct(
                    $name,
                    $variant['price'],
                    $variant['sku'],
                    $dbVariant->id,
                    $variant['cost_price'],
                    $image ?? data_get($ISProduct, 'images.0.large_url'),
                    Weight::fromKilos($variant['weight']),
                    Dimensions::fromInsalesDimensions($variant['dimensions']),
                    [$dbVariant->ean13]
                )->json();

                $dbVariant->update(['cdek_id' => $cdekProduct['id']]);

                $variants[$dbVariant->id] = 'создан';
            } else {
                FullfillmentApi::updateSimpleProduct($dbVariant->cdek_id, [
                    'name' => $name,
                    'article' => $variant['sku'],
                    'price' => $variant['price'],
                    'extId' => $dbVariant->id,
                    'purchasingPrice' => $variant['cost_price'],
                    'image' => data_get($ISProduct, 'images.0.large_url'),
                    'weight' => Weight::fromKilos($variant['weight']),
                    'dimensions' => Dimensions::fromInsalesDimensions($variant['dimensions']),
                    'barcodes' => [$dbVariant->ean13],
                ]);

                $variants[$dbVariant->id] = 'обновлен';
            }
        }

        $this->info('ok');
        foreach ($variants as $id => $status) {
            $this->info("Модификация id $id - $status");
        }
    }

    private function syncByInsalesId(): void
    {
        $ISProduct = InSalesApi::getProduct($this->option('ISid'))->json();
        $dbProduct = Product::where('insales_id', $this->option('ISid'))->first();

        if ($ISProduct['archived']) {
            $this->error('Товар архивный в инсейлс');
        }

        if (is_null($dbProduct)) {
            $this->info("Создание нового товара. IS id {$this->option('ISid')}");
            // dispatch(new CreateProductFromInsales($ISProduct, false));

            return;
        }

        $this->info("Синхронизация $dbProduct->name. id $dbProduct->id. IS id $dbProduct->insales_id");

        // dispatch(new UpdateProductFromInsales($ISProduct));
    }
}
