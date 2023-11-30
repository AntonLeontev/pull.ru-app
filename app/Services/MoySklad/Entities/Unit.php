<?php

namespace App\Services\MoySklad\Entities;

readonly class Unit extends AbstractEntity
{
    public function __construct(public ?string $id)
    {
    }

    public function jsonSerialize(): ?array
    {
        if (is_null($this->id)) {
            return null;
        }

        return [
            'meta' => [
                'href' => 'https://api.moysklad.ru/api/remap/1.2/entity/uom/'.$this->id,
                'type' => 'uom',
            ],
        ];
    }

    public static function fromInsalesUnit(string $unit): static
    {
        $id = self::getIdByInsalesUnit($unit);

        return new static($id);
    }

    private static function getIdByInsalesUnit(string $unit)
    {
        return match ($unit) {
            'pce' => '19f1edc0-fc42-4001-94cb-c9ec9c62ec10',
            default => null,
        };
    }
}
