<?php

namespace App\Services\CDEK\Entities\Delivery;

use JsonSerializable;

readonly class Item implements JsonSerializable
{
    /**
     * @param  string  $name Наименование товара
     * @param  string  $wareKey Идентификатор/артикул товара
     * @param  float  $price Оплата за товар при получении (за единицу товара в валюте страны получателя, значение >=0) — наложенный платеж,
     * в случае предоплаты значение = 0
     * @param  float  $cost Объявленная стоимость товара (за единицу товара в валюте взаиморасчетов, значение >=0).
     * С данного значения рассчитывается страховка
     * @param  float  $weight Вес (за единицу товара, в граммах)
     * @param  int  $amount Количество единиц товара (в штуках) Количество одного товара в заказе может быть от 1 до 999
     */
    public function __construct(
        public string $name,
        public string $wareKey,
        public float $price,
        public float $cost,
        public float $weight,
        public int $amount,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'ware_key' => $this->wareKey,
            'payment' => ['value' => $this->price],
            'cost' => $this->cost,
            'weight' => $this->weight,
            'amount' => $this->amount,
        ];
    }
}
