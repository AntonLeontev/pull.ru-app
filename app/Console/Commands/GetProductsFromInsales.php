<?php

namespace App\Console\Commands;

use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Jobs\CreateProductFromInsales;
use Src\Domain\Synchronizer\Jobs\UpdateProductFromInsales;
use Src\Domain\Synchronizer\Models\Product;

class GetProductsFromInsales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-products';

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
        $this->call('app:sync-categories');
        $this->call('app:sync-options');

        $products = InSalesApi::getProducts(null, 100)->json();

        $count = 0;
        foreach ($products as $ISProduct) {
            if ($ISProduct['archived']) {
                continue;
            }

            $product = Product::where(['insales_id' => $ISProduct['id']])->first();

            if ($product) {
                dispatch(new UpdateProductFromInsales($ISProduct));
            } else {
                dispatch(new CreateProductFromInsales($ISProduct, false));
            }

            $count++;
        }

        $this->info("Добавлено в очередь $count товаров");
    }
}
