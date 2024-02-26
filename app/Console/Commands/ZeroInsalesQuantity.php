<?php

namespace App\Console\Commands;

use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Models\Product;

class ZeroInsalesQuantity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:zero-insales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Выставляет остатки всех товаров на 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = collect(InSalesApi::getProducts(perPage: 1000)->json());

        if ($products->count() === 1000) {
            $this->alert('Загружено 1000 товаров. Нужно заняться пагинацией');
        }

        $products->filter(function ($item) {
            return $item['is_hidden'] === false && $item['archived'] === false;
        });

        $progressbar = $this->output->createProgressBar($products->count());

        $progressbar->start();

        foreach ($products as $product) {
            foreach ($product['variants'] as $variant) {
                InSalesApi::updateVariant($product['id'], $variant['id'], [
                    'quantity_at_warehouse0' => 0,
                ]);
            }

            $dbProduct = Product::where('insales_id', $product['id'])->first();

            cache(['blocked_products.'.$dbProduct->id => true]);
            $progressbar->advance();
        }

        $progressbar->finish();

        return self::SUCCESS;
    }
}
