<?php

namespace Src\Domain\Synchronizer\Enums;

use App\Services\MoySklad\Entities\OrderState;
use Exception;
use Illuminate\Support\Collection;

enum OrderStatus: string
{
    case init = 'init';
    case approved = 'approved';
    case assembling = 'assembling';
    case assembled = 'assembled';
    case dispatched = 'dispatched';
    case delivered = 'delivered';
    case canceled = 'canceled';
    case returned = 'returned';
    case returning = 'returning';
    case partlyDelivered = 'partly_delivered';

    public static function fromCdek(string $state): static
    {
        $status = self::findState('cdekff', $state);

        if (is_null($status)) {
            throw new Exception("Неизвестный статус сдека: $state");
        }

        return self::from($status['app']);
    }

    public static function fromInsales(string $state): static
    {
        $status = self::findState('insales', $state);

        if (is_null($status)) {
            throw new Exception("Неизвестный статус Insales: $state");
        }

        return self::from($status['app']);
    }

    public function toMS(): OrderState
    {
        $state = static::findState('app', $this->value);

        return new OrderState($state['ms']);
    }

    public function toInsales(): string
    {
        $state = static::findState('app', $this->value);

        return $state['insales'];
    }

    public function toCdekff(): string
    {
        $state = static::findState('app', $this->value);

        return $state['cdekff'];
    }

    private static function findState(string $type, string $state): ?array
    {
        return static::states()->first(function ($item) use ($type, $state) {
            if (! isset($item[$type])) {
                return false;
            }

            return $item[$type] === $state;
        });
    }

    private static function states(): Collection
    {
        return collect([
            [
                'app' => 'init',
                'insales' => 'novyy',
            ],
            [
                'app' => 'approved',
                'insales' => 'soglasovan',
                'cdekff' => 'pending_queued',
                'ms' => '400c639b-ad4b-11ee-0a80-0dfd005ae9c3',
            ],
            [
                'app' => 'approved',
                'insales' => 'soglasovan',
                'cdekff' => 'confirmed',
                'ms' => '400c639b-ad4b-11ee-0a80-0dfd005ae9c3',
            ],
            [
                'app' => 'assembling',
                'insales' => 'v-sborke',
                'cdekff' => 'assembling',
                'ms' => '400c639b-ad4b-11ee-0a80-0dfd005ae9c3',
            ],
            [
                'app' => 'assembled',
                'insales' => 'sobran-2',
                'cdekff' => 'assembled',
                'ms' => '400c63ea-ad4b-11ee-0a80-0dfd005ae9c4',
            ],
            [
                'app' => 'dispatched',
                'insales' => 'dostavlyaetsya-2',
                'cdekff' => 'delivery',
                'ms' => '400c6431-ad4b-11ee-0a80-0dfd005ae9c5',
            ],
            [
                'app' => 'dispatched',
                'insales' => 'dostavlyaetsya-2',
                'cdekff' => 'processing',
                'ms' => '400c6431-ad4b-11ee-0a80-0dfd005ae9c5',
            ],
            [
                'app' => 'delivered',
                'insales' => 'dostavlen',
                'cdekff' => 'complete',
                'ms' => '400c64b6-ad4b-11ee-0a80-0dfd005ae9c6',
            ],
            [
                'app' => 'canceled',
                'insales' => 'otmenen',
                'cdekff' => 'cancel',
                'ms' => '400c6545-ad4b-11ee-0a80-0dfd005ae9c8',
            ],
            [
                'app' => 'returning',
                'insales' => 'v-protsesse-vozvrata',
                'cdekff' => 'returning',
                'ms' => '400c6431-ad4b-11ee-0a80-0dfd005ae9c5',
            ],
            [
                'app' => 'returned',
                'insales' => 'vozvrat',
                'cdekff' => 'return',
                'ms' => '400c6500-ad4b-11ee-0a80-0dfd005ae9c7',
            ],
            [
                'app' => 'partly_delivered',
                'insales' => 'chastichno-dostavlen',
                // 'cdekff' => 'return',
                // 'ms' => '400c6500-ad4b-11ee-0a80-0dfd005ae9c7',
            ],
        ]);
    }
}
