<?php

namespace Src\Domain\Synchronizer\Events;

class VariantFromMoySkladToCdekError extends AbstractEventForLogging
{
    public function __construct(public string $name, public int $id) {}

    public function getMessage(): string
    {
        return "❌Мой Склад -> СДЭК\nтовар: {$this->name} ({$this->id})";
    }
}
