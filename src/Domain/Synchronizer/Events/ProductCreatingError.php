<?php

namespace Src\Domain\Synchronizer\Events;

class ProductCreatingError extends AbstractEventForLogging
{
    public function __construct(public string $productName)
    {
    }

    public function getMessage(): string
    {
        return "❌ Ошибка создания товара \"{$this->productName}\"";
    }
}
