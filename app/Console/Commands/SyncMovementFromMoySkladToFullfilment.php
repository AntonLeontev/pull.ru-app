<?php

namespace App\Console\Commands;

use App\Services\CDEK\Entities\MovementProduct;
use App\Services\CDEK\FullfillmentApi;
use App\Services\MoySklad\MoySkladApi;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Src\Domain\Synchronizer\Models\Movement;
use Src\Domain\Synchronizer\Models\Variant;

class SyncMovementFromMoySkladToFullfilment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-movement-ms-ff {--id=}';

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
        if (is_null($this->option('id'))) {
            $msMovement = MoySkladApi::getMoves(1, 'positions,positions.assortment')->json('rows.0');

            $sum = $msMovement['sum'] / 100;
            $created = Carbon::parse($msMovement['created'])->format('d.m.Y');
            $answer = $this->ask("Последнее перемещние {$msMovement['name']} от {$created} на сумму {$sum}р (y/n)");

            if ($answer !== 'y') {
                return;
            }
        } else {
            $msMovement = MoySkladApi::getMove($this->option('id'));
        }

        $movement = Movement::where('moy_sklad_id', $msMovement['id'])->first();

        if (! is_null($movement)) {
            if ($movement->cdek_id !== null) {
                FullfillmentApi::deleteMovement($movement->cdek_id);
            }
        } else {
            $movement = Movement::create(['moy_sklad_id' => $msMovement['id']]);
        }

        $cdekMovement = FullfillmentApi::createMovement(config('services.cdekff.warehouse'), $movement->id)->json();

        $movement->update(['cdek_id' => $cdekMovement['id']]);

        //----------------- Positions----------------

        $response = MoySkladApi::getMovePositions($msMovement['id'], expand: 'assortment')->json();

        if ($response['meta']['size'] === 1000) {
            $this->alert('У перемещения 1000 или более позиций!');

            return;
        }

        $variantsIds = [];
        foreach ($response['rows'] as $row) {
            if ($row['assortment']['meta']['type'] === 'variant') {
                $variantsIds[$row['assortment']['id']] = $row['quantity'];
            }

            if ($row['assortment']['meta']['type'] === 'product') {
                throw new Exception("Найден товар, а не модификация в перемещении ID товара в МС: {$row['id']}", 1);
            }
        }

        $variants = Variant::whereIn('moy_sklad_id', array_keys($variantsIds))->get(['cdek_id', 'ean13', 'moy_sklad_id']);

        $cdekProducts = [];
        foreach ($variants as $variant) {
            $cdekProducts[] = new MovementProduct(
                $variant->cdek_id,
                config('services.cdekff.shop'),
                $cdekMovement['id'],
                $variant->ean13,
                $variantsIds[$variant->moy_sklad_id]
            );
        }

        FullfillmentApi::addProductsToMovement($cdekProducts);

        $count = count($cdekProducts);
        $this->info("Создано перемещение {$cdekMovement['id']}. Добавлено товаров: $count из {$response['meta']['size']}");
    }
}
