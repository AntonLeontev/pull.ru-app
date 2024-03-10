<?php

namespace Src\Domain\Synchronizer\Enums;

enum OrderPaymentType: string
{
    case online = 'online';
    case deliver = 'deliver';

    public static function fromInsales($paymentId): static
    {
        return match ($paymentId) {
            config('services.inSales.online_payment_gateway_id') => self::online,
            config('services.inSales.deliver_payment_gateway_id') => self::deliver,
            default => self::deliver,
        };
    }
}
