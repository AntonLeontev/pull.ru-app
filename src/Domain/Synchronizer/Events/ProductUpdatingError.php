<?php

namespace Src\Domain\Synchronizer\Events;

class ProductUpdatingError extends AbstractEventForLogging
{
    public function __construct(public string $productName)
    {
    }

    public function getMessage(): string
    {
        return "❌ Ошибка обновления товара \"{$this->productName}\"";
    }
}
