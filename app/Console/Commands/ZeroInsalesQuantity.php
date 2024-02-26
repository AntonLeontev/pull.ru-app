<?php

namespace App\Console\Commands;

use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
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

        $filtered = $products->filter(function ($item) {
            return $item['archived'] === false;
        });

        $progressbar = $this->output->createProgressBar($filtered->count());

        $progressbar->start();

        foreach ($filtered as $product) {
            foreach ($product['variants'] as $variant) {
                try {
                    InSalesApi::updateVariant($product['id'], $variant['id'], [
                        'quantity_at_warehouse0' => 0,
                    ]);
                } catch (RequestException $e) {
                    if ($e->getCode() === 404) {
                        $this->info("404 on product id {$product['id']}, variant id {$variant['id']}");

                        continue;
                    }
                }
            }

            $dbProduct = Product::where('insales_id', $product['id'])->first();

            cache(['blocked_products.'.$dbProduct->id => true]);
            $progressbar->advance();
        }

        $progressbar->finish();

        return self::SUCCESS;
    }
}
