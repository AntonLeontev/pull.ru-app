<?php

namespace App\Services\MoySklad\Entities;

readonly class Characteristic extends AbstractEntity
{
    public function __construct(public string $id, public string $value) {}

    public function jsonSerialize(): ?array
    {
        if (is_null($this->id)) {
            return null;
        }

        return [
            'id' => $this->id,
            'value' => $this->value,
        ];
    }
}
