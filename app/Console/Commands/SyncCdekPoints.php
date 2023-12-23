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
                        'name' => $point['name'],
                        'uuid' => $point['uuid'],
                        'work_time' => $point['work_time'],
                        'type' => $point['type'],
                        'owner_code' => $point['owner_code'],
                        'take_only' => $point['take_only'],
                        'is_handout' => $point['is_handout'],
                        'is_reception' => $point['is_reception'],
                        'is_dressing_room' => $point['is_dressing_room'],
                        'is_ltl' => $point['is_ltl'],
                        'have_cashless' => $point['have_cashless'],
                        'have_cash' => $point['have_cash'],
                        'allowed_cod' => $point['allowed_cod'],
                        'weight_min' => $point['weight_min'] ?? null,
                        'weight_max' => $point['weight_max'] ?? null,
                        'country_code' => $point['location']['country_code'],
                        'region_code' => $point['location']['region_code'],
                        'region' => $point['location']['region'],
                        'city_code' => $point['location']['city_code'],
                        'city' => $point['location']['city'],
                        'fias_guid' => $point['location']['fias_guid'],
                        'postal_code' => $point['location']['postal_code'],
                        'longitude' => $point['location']['longitude'],
                        'latitude' => $point['location']['latitude'],
                        'address' => $point['location']['address'],
                        'address_full' => $point['location']['address_full'],
                        'fulfillment' => $point['fulfillment'],
                    ]
                );

                if (isset($point['dimensions'])) {
                    $cdekPoint->dimensions = json_encode($point['dimensions']);
                    $cdekPoint->save();
                }
            }
        }
    }
}
