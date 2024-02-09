<?php

namespace App\Console\Commands;

use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Jobs\CreateProductFromInsales;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromInsales;
use Src\Domain\Synchronizer\Models\Product;

class SyncProductFromInsales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-product {--id=} {--ISid=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->didReceiveOptions($this->input)) {
            $this->error('Нужно указать --id или --ISid');
        }

        if ($this->option('id')) {
            $this->syncById();

            return;
        }

        if ($this->option('ISid')) {
            $this->syncByInsalesId();
        }
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

        dispatch(new UpdateProductFromInsales($ISProduct));
    }

    private function syncByInsalesId()
    {
        $ISProduct = InSalesApi::getProduct($this->option('ISid'))->json();
        $dbProduct = Product::where('insales_id', $this->option('ISid'))->first();

        if ($ISProduct['archived']) {
            $this->error('Товар архивный в инсейлс');
        }

        if (is_null($dbProduct)) {
            $this->info("Создание нового товара. IS id {$this->option('ISid')}");
            dispatch(new CreateProductFromInsales($ISProduct, false));

            return;
        }

        $this->info("Синхронизация $dbProduct->name. id $dbProduct->id. IS id $dbProduct->insales_id");

        dispatch(new UpdateProductFromInsales($ISProduct));
    }
}
