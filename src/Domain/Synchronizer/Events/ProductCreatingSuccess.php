<?php

namespace Src\Domain\Synchronizer\Events;

class ProductCreatingSuccess extends AbstractEventForLogging
{
    public function __construct(public string $productName, public string $permalink)
    {
    }

    public function getMessage(): string
    {
        return "✅ Успешно создан товар \"{$this->productName}\" https://pull.ru/product/{$this->permalink}";
    }
}
