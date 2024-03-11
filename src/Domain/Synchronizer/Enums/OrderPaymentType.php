<?php

namespace Src\Domain\Synchronizer\Enums;

enum OrderPaymentType: string
{
    case online = 'online';
    case deliver = 'deliver';

    public static function fromInsales($paymentId): static
    {
        return match (true) {
            config('services.inSales.online_payment_gateway_id') == $paymentId => self::online,
            config('services.inSales.deliver_payment_gateway_id') == $paymentId => self::deliver,
            default => self::deliver,
        };
    }
}
