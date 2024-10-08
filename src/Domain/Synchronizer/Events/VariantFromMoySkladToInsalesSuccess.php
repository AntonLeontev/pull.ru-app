<?php

namespace Src\Domain\Synchronizer\Events;

class VariantFromMoySkladToInsalesSuccess extends AbstractEventForLogging
{
    public function __construct(public string $name, public int $id) {}

    public function getMessage(): string
    {
        return "✅Мой Склад -> Insales\nтовар: {$this->name} (ID: {$this->id})";
    }
}
