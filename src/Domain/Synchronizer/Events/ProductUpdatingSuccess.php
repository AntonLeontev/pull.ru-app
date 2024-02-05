<?php

namespace Src\Domain\Synchronizer\Events;

class ProductUpdatingSuccess extends AbstractEventForLogging
{
    public function __construct(public string $productName, public string $permalink)
    {
    }

    public function getMessage(): string
    {
        return "✅ Успешно обновлен товар \"{$this->productName}\" https://limmite.ru/product/{$this->permalink}";
    }
}
