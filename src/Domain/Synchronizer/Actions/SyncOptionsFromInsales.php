<?php

namespace Src\Domain\Synchronizer\Actions;

use Illuminate\Support\Facades\DB;
use Src\Domain\InSales\Services\InSalesApi;
use Src\Domain\MoySklad\Services\MoySkladApi;
use Src\Domain\Synchronizer\Models\Option;

class SyncOptionsFromInsales
{
    public function handle()
    {
        $insalesOptions = InSalesApi::getOptionNames()->json();

        foreach ($insalesOptions as $option) {
            DB::transaction(function () use ($option) {
                $dbOption = Option::updateOrCreate(
                    ['insales_id' => $option['id']],
                    ['name' => $option['title']]
                );

                // Изменить характеристику в Мой Склад нельзя
                if (! is_null($dbOption->moy_sklad_id)) {
                    return;
                }

                MoySkladApi::createCharacteristic($dbOption->name);
            });
        }
    }
}
