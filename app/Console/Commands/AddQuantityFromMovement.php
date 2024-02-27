<?php

namespace App\Console\Commands;

use App\Services\CDEK\FullfillmentApi;
use App\Services\InSales\InSalesApi;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Src\Domain\Synchronizer\Models\Product;
use Src\Domain\Synchronizer\Models\Variant;

class AddQuantityFromMovement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-quantity-from-movement {--cdek_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавляет товары из перемещения на склад фуллфилмента';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $cdekId = $this->option('cdek_id');

        if (is_null($cdekId)) {
            $cdekId = $this->askCdekId();
        }

        $response = FullfillmentApi::getMovementProducts($cdekId);
        $lastPage = $response->json('page_count');

        $this->addQuantityToInsales($response->json('_embedded.document_item_id'));

        if ($lastPage = 1) {
            return;
        }

        $progress = $this->output->createProgressBar($lastPage);
        $progress->advance();

        foreach (range(2, $lastPage) as $page) {
            $response = FullfillmentApi::getMovementProducts($cdekId, $page);

            $this->addQuantityToInsales($response->json('_embedded.document_item_id'));

            $progress->advance();
        }

        $progress->finish();
    }

    private function addQuantityToInsales(array $products): void
    {
        $import = ['variants' => []];
        $warehouses = collect(config('sync.warehouses_match'));
        $cdekWarehouse = $products[0]['_embedded']['warehouse']['id'];
        $insalesWarehouse = $warehouses->first(fn ($warehouse) => $warehouse['cdekff'] === $cdekWarehouse)['insales'];

        foreach ($products as $product) {
            $id = (int) $product['_embedded']['productOffer']['extId'];
            [$productId, $variantId] = $this->getInsalesIds($id);
            $quantityInInsales = $this->getQuantityInInsales($productId, $variantId, $insalesWarehouse);

            $insalesVariant = [
                'id' => $variantId,
                'quantity_at_warehouse'.$insalesWarehouse => $product['quantityPlace'] + $quantityInInsales,
            ];

            $import['variants'][] = $insalesVariant;
        }

        dd($import['variants']);

        $response = InSalesApi::updateVariantsGroup($import)->json();
    }

    private function askCdekId(): int
    {
        $cdekMovements = [];
        $cdekResponse = FullfillmentApi::getMovements();
        $lastPage = $cdekResponse->json('page_count');

        if ($lastPage > 1) {
            $movements = FullfillmentApi::getMovements($lastPage - 1)->json('_embedded.movement_acceptance');
            $cdekMovements = $movements;
        }

        $movements = FullfillmentApi::getMovements($lastPage)->json('_embedded.movement_acceptance');
        $cdekMovements = [...$cdekMovements, ...$movements];

        $choices = [];

        foreach ($cdekMovements as $movement) {
            if ($movement['state'] !== 'closed') {
                continue;
            }
            $date = Carbon::parse($movement['created']['date'])->format('d.m.Y');
            $choices[] = trim("ID [{$movement['id']}] от {$date}. {$movement['comment']}");
        }

        $choice = $this->output->choice(
            'Какое перемещение импортируем?',
            $choices,
        );

        return (int) str($choice)->match('~\[\d+\]~')->trim('[]')->value();
    }

    private function getInsalesId(int $id): array
    {
        $variant = Variant::find($id);
        $product = Product::find($variant->product_id);

        return [$product->insales_id, $variant->insales_id];
    }

    private function getQuantityInInsales(int $productId, int $variantId, int $warehouse): int
    {
        return (int) InSalesApi::getVariant($productId, $variantId)->json("quantity_at_warehouse$warehouse");
    }
}
