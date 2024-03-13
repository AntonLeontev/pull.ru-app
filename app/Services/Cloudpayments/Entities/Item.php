<?php

namespace App\Services\Cloudpayments\Entities;

use App\Services\Cloudpayments\Enums\AgentSign;
use JsonSerializable;

/**
 * Параметры услуг/товарных позиций в чеке.
 */
class Item implements JsonSerializable
{
    /**
     * @param  string  $label Наименование товара или услуги
     * @param  string  $price Цена за единицу товара/услуги
     * @param  string  $quantity Количество
     * @param  string  $vat Ставка НДС услуги/товара
     * @param  string  $amount Price * Quantity c учетом скидки
     * @param  AgentSign|null  $agentSign Признак агента, тег ОФД 1222
     * @param  PurveyorData|null  $purveyorData Данные поставщика платежного агента, тег ОФД 1224
     */
    public function __construct(
        public string $label,
        public string $price,
        public string $quantity,
        public string $vat,
        public string $amount,
        public ?AgentSign $agentSign = null,
        public ?PurveyorData $purveyorData = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'Label' => $this->label,
            'Price' => $this->price,
            'Quantity' => $this->quantity,
            'Vat' => $this->vat,
            'Amount' => $this->amount,
            'AgentSign' => $this->agentSign,
            'PurveyorData' => $this->purveyorData,
        ];
    }
}
