<?php

namespace Src\Domain\MoySklad\Entities;

class Characteristic extends AbstractEntity
{
    public function __construct(public readonly string $id, public readonly string $value)
    {
    }

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
