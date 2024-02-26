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
        $warehouses = collect(config('sync.warehouses_match'));

        foreach ($variants as $variant) {
            if (in_array($variant['extId'], config('sync.cdekff_not_allowed'))) {
                continue;
            }

            $insalesVariant = [];
            $dbVariant = Variant::find($variant['extId']);

            if (is_null($dbVariant)) {
                $this->info("Не найдена модификация с id {$variant['extId']}. Ищу по cdek_id");
                $dbVariant = Variant::where('cdek_id', $variant['id'])->first(['insales_id']);

                if (is_null($dbVariant)) {
                    $this->info("Не найдена модификация с cdek_id {$variant['id']}\n");

                    continue;
                } else {
                    $this->info("Нашел со cdek_id {$variant['id']}\n");
                }
            }

            $insalesVariant['id'] = $dbVariant->insales_id;

            $items = collect($variant['items'] ?? []);

            // Только доступные для заказа
            $normalItems = $items->filter(fn ($item) => $item['state'] === 'normal');

            foreach ($warehouses as $warehouse) {
                $item = $normalItems->first(fn ($item) => $item['warehouse'] === $warehouse['cdekff']);

                if (! is_null($item)) {
                    $insalesVariant['quantity_at_warehouse'.$warehouse['insales']] = $item['count'];
                } else {
                    $insalesVariant['quantity_at_warehouse'.$warehouse['insales']] = 0;
                }
            }

            $data['variants'][] = $insalesVariant;
        }

        if (empty($data['variants'])) {
            return;
        }

        $response = InSalesApi::updateVariantsGroup($data)->json();

        foreach ($response as $result) {
            if ($result['status'] === 'ok') {
                continue;
            }

            $error = '';
            foreach ($result['errors'] as $key => $value) {
                $error .= trim("$key - ".implode(' ', $value));
            }

            $this->info("Ошибка обновления. insales_id модификации {$result['id']}. Ошибка: $error\n");
        }
    }
}
