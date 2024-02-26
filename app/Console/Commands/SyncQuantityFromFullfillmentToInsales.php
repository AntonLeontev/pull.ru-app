<?php

namespace App\Console\Commands;

use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;
use Src\Domain\Synchronizer\Models\Variant;

class SyncQuantityFromFullfillmentToInsales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-quantity-ff-insales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Выгружает остатки по складам из сдек ФФ в инсейлс';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = FullfillmentApi::getProducts();
        $pageCount = $response->json('page_count');

        $progressbar = $this->output->createProgressBar($pageCount);
        $progressbar->start();

        $ffVarians = $response->json('_embedded.product_offer');
        $this->syncVariants($ffVarians);

        $progressbar->advance();

        foreach (range(2, $pageCount) as $page) {
            $response = FullfillmentApi::getProducts($page);
            $ffVarians = $response->json('_embedded.product_offer');
            $this->syncVariants($ffVarians);

            $progressbar->advance();
        }

        $progressbar->finish();

        return self::SUCCESS;
    }

    private function syncVariants(array $variants): void
    {
        $data = ['variants' => []];
        $warehouses = collect(config('services.warehouses_match'));

        foreach ($variants as $variant) {
            $insalesVariant = [];
            $dbVariant = Variant::find($variant['extId']);
            $insalesVariant['id'] = $dbVariant->insales_id;

            $items = $variant['items'];

            foreach ($items as $item) {
                $configWarehouse = $warehouses->first(fn ($warehouse) => $item['warehouse'] === $warehouse['cdekff']);

                $insalesVariant['quantity_at_warehouse'.$configWarehouse['insales']] = $item['count'];
            }

            $data['variants'][] = $insalesVariant;
        }
        dd($data);
        InSalesApi::updateVariantsGroup($data);
    }
}
