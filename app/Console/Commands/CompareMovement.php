<?php

namespace App\Console\Commands;

use App\Services\CDEK\FullfillmentApi;
use App\Services\MoySklad\MoySkladApi;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Src\Domain\Synchronizer\Models\Movement;
use Src\Domain\Synchronizer\Models\Variant;

class CompareMovement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:compare-movement {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет все ли товары приняты в перемещении МС и сдек';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->option('id');

        if (is_null($id)) {
            $id = $this->askId();
        }

        $movement = Movement::find($id);

        $productsInCdek = $this->getProductsInCdek($movement->cdek_id);
        $productsInMS = $this->getProductsInMS($movement->moy_sklad_id);
    }

    private function askId(): int
    {
        $movements = Movement::query()
            ->take(5)
            ->latest()
            ->get();

        if ($movements->isEmpty()) {
            throw new Exception('В базе нет перемещений');
        }

        $choices = [];

        foreach ($movements as $movement) {
            $date = $movement->created_at->format('d.m.Y');
            $choices[] = trim("ID [{$movement->id}] от {$date}");
        }

        $choice = $this->output->choice(
            'Какое перемещение проверить?',
            $choices,
        );

        return (int) str($choice)->match('~\[\d+\]~')->trim('[]')->value();
    }

    private function getProductsInCdek(int $id): Collection
    {
        $this->info('Собираем продукты из сдека');

        $products = [];
        $response = FullfillmentApi::getMovementProducts($id);
        $lastPage = $response->json('page_count');

        $progress = $this->output->createProgressBar($lastPage);
        $progress->start();

        foreach (range(1, $lastPage) as $page) {
            $response = FullfillmentApi::getMovementProducts($id, $page);

            $cdekProducts = $response->json('_embedded.document_item_id');

            foreach ($cdekProducts as $cdekProduct) {
                $product = [
                    'id' => $cdekProduct['_embedded']['productOffer']['extId'],
                    'qnt' => $cdekProduct['quantityPlace'],
                ];

                $products[] = $product;
            }

            $progress->advance();
        }

        $progress->finish();

        return collect($products);
    }

    private function getProductsInMS(string $id)
    {
        $this->info('Собираем продукты из МС');

        $products = [];
        $limit = 500;
        $offset = -500;

        $progress = $this->output->createProgressBar();

        do {
            $offset += $limit;
            $response = MoySkladApi::getMovePositions($id, $limit, $offset, 'assortment');
            $size = $response->json('meta.size');

            $progress->start($size);

            foreach ($response->json('rows') as $msProduct) {
                $productId = Variant::where('moy_sklad_id', $msProduct['assortment']['id'])->first()->id;

                $product = [
                    'id' => $productId,
                    'qnt' => $msProduct['quantity'],
                ];

                $products[] = $product;
            }

            $progress->advance($limit);
        } while ($size > $limit + $offset);

        $progress->finish();

        return collect($products);
    }
}
