<?php

namespace App\Services\Cloudpayments\Entities;

use JsonSerializable;

class Amounts implements JsonSerializable
{
    /**
     * Undocumented function
     *
     * @param  string|null  $electronic Сумма оплаты электронными деньгами
     * @param  string|null  $advancePayment Сумма предоплаты
     * @param  string|null  $credit Сумма постоплатой
     * @param  string|null  $provision Сумма встречным предоставлением
     */
    public function __construct(
        private string $electronic = '0',
        private string $advancePayment = '0',
        private string $credit = '0',
        private string $provision = '0',
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'Electronic' => $this->electronic,
            'AdvancePayment' => $this->advancePayment,
            'Credit' => $this->credit,
            'Provision' => $this->provision,
        ];
    }
}
