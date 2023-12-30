<?php

namespace App\Console\Commands;

use App\Services\CDEK\CdekApi;
use Illuminate\Console\Command;
use Src\Domain\Delivery\Models\CdekPoint;

class SyncCdekPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cdek-points';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (array_values(config('delivery.cdek.allowed_regions')) as $code) {
            $points = CdekApi::deliverypoints(['region_code' => $code])->json();

            foreach ($points as $point) {
                $cdekPoint = CdekPoint::updateOrCreate(
                    ['uuid' => $point['uuid']],
                    [
                        'code' => $point['code'],
                        'name' => $point['name'] ?? null,
                        'work_time' => $point['work_time'] ?? null,
                        'type' => $point['type'] ?? null,
                        'owner_code' => $point['owner_code'] ?? null,
                        'take_only' => $point['take_only'] ?? null,
                        'is_handout' => $point['is_handout'] ?? null,
                        'is_reception' => $point['is_reception'] ?? null,
                        'is_dressing_room' => $point['is_dressing_room'] ?? null,
                        'is_ltl' => $point['is_ltl'] ?? null,
                        'have_cashless' => $point['have_cashless'] ?? null,
                        'have_cash' => $point['have_cash'] ?? null,
                        'allowed_cod' => $point['allowed_cod'] ?? null,
                        'weight_min' => $point['weight_min'] ?? null,
                        'weight_max' => $point['weight_max'] ?? null,
                        'country_code' => $point['location']['country_code'] ?? null,
                        'region_code' => $point['location']['region_code'] ?? null,
                        'region' => $point['location']['region'] ?? null,
                        'city_code' => $point['location']['city_code'] ?? null,
                        'city' => $point['location']['city'] ?? null,
                        'fias_guid' => $point['location']['fias_guid'] ?? null,
                        'postal_code' => $point['location']['postal_code'] ?? null,
                        'longitude' => $point['location']['longitude'] ?? null,
                        'latitude' => $point['location']['latitude'] ?? null,
                        'address' => $point['location']['address'] ?? null,
                        'address_full' => $point['location']['address_full'] ?? null,
                        'fulfillment' => $point['fulfillment'] ?? null,
                    ]
                );

                if (isset($point['dimensions'])) {
                    $cdekPoint->dimensions = json_encode($point['dimensions']);
                    $cdekPoint->save();
                }
            }
        }

        $deletedCount = 0;

        CdekPoint::lazyByid()->each(function (CdekPoint $point) use ($deletedCount) {
            if ($point->updated_at < today()) {
                $point->delete();
                $deletedCount++;
            }
        });

        $this->comment("Удалено $deletedCount ПВЗ");
    }
}
