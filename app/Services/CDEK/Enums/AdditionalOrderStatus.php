<?php

namespace App\Services\CDEK\Enums;

enum AdditionalOrderStatus: int
{
    case status1 = 1;
    case status2 = 2;
    case status3 = 3;
    case status4 = 4;
    case status5 = 5;
    case status6 = 6;
    case status7 = 7;
    case status8 = 8;
    case status9 = 9;
    case status10 = 10;
    case status11 = 11;
    case status12 = 12;
    case status13 = 13;
    case status14 = 14;
    case status15 = 15;
    case status16 = 16;
    case status17 = 17;
    case status18 = 18;
    case status19 = 19;
    case status20 = 20;
    case status21 = 21;
    case status22 = 22;
    case status23 = 23;
    case status24 = 24;
    case status25 = 25;
    case status26 = 26;
    case status27 = 27;
    case status31 = 31;
    case status32 = 32;
    case status33 = 33;

    public function reason(): string
    {
        return match (true) {
            $this === self::status1 => 'Возврат, неверный адрес',
            $this === self::status2 => 'Возврат, не дозвонились',
            $this === self::status3 => 'Возврат, адресат не проживает',
            $this === self::status4 => 'Возврат, не должен выполняться: вес отличается от заявленного более, чем на X г.',
            $this === self::status5 => 'Возврат, не должен выполняться: фактически нет отправления (на бумаге есть)',
            $this === self::status6 => 'Возврат, не должен выполняться: дубль номера заказа в одном акте приема-передачи',
            $this === self::status7 => 'Возврат, не должен выполняться: не доставляем в данный город/регион',
            $this === self::status8 => 'Возврат, повреждение упаковки, при приемке от отправителя',
            $this === self::status9 => 'Возврат, повреждение упаковки, у перевозчика',
            $this === self::status10 => 'Возврат, повреждение упаковки, на нашем складе/доставке у курьера',
            $this === self::status11 => 'Возврат, отказ от получения: Без объяснения',
            $this === self::status12 => 'Возврат, отказ от получения: Претензия к качеству товара',
            $this === self::status13 => 'Возврат, отказ от получения: Недовложение',
            $this === self::status14 => 'Возврат, отказ от получения: Пересорт',
            $this === self::status15 => 'Возврат, отказ от получения: Не устроили сроки',
            $this === self::status16 => 'Возврат, отказ от получения: Уже купил',
            $this === self::status17 => 'Возврат, отказ от получения: Передумал',
            $this === self::status18 => 'Возврат, отказ от получения: Ошибка оформления',
            $this === self::status19 => 'Возврат, отказ от получения: Повреждение упаковки, у получателя',
            $this === self::status20 => 'Частичная доставка',
            $this === self::status21 => 'Возврат, отказ от получения: Нет денег',
            $this === self::status22 => 'Возврат, отказ от получения: Товар не подошел/не понравился',
            $this === self::status23 => 'Возврат, истек срок хранения',
            $this === self::status24 => 'Возврат, не прошел таможню',
            $this === self::status25 => 'Возврат, не должен выполняться: является коммерческим грузом',
            $this === self::status26 => 'Утерян',
            $this === self::status27 => 'Не востребован, утилизация',
            $this === self::status31 => 'Возврат, по запросу отправителя',
            $this === self::status32 => 'Возврат, по запросу плательщика',
            $this === self::status33 => 'Возврат, СНТ не получено',
            default => 'Неизвестный статус',
        };
    }

    public function __toString(): string
    {
        return $this->reason();
    }
}
