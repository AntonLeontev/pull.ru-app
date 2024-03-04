<?php

namespace App\Console\Commands;

use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;

class SetSimilar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-similar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ставит похожие товары в инсейлс по артикулу';

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

        $productsCollection = collect();

        foreach ($filtered as $product) {
            $productsCollection->push([
                'id' => $product['id'],
                'sku' => data_get($product, 'variants.0.sku'),
            ]);
        }

        $progressbar = $this->output->createProgressBar($productsCollection->count());

        $progressbar->start();

        foreach ($productsCollection as $key => $product) {
            $similars = $productsCollection->filter(fn ($item) => $item['sku'] === $product['sku']);

            $progressbar->advance();

            if ($similars->count() === 1) {
                $productsCollection->pull($key);

                continue;
            }

            InSalesApi::createSimilar($product['id'], $similars->pluck('id')->toArray());
        }

        $progressbar->finish();

        return $this::SUCCESS;
    }
}
