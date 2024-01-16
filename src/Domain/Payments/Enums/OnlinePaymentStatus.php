<?php

namespace Src\Domain\Payments\Enums;

enum OnlinePaymentStatus: string
{
    case new = 'NEW';
    case authorized = 'AUTHORIZED';
    case confirmed = 'CONFIRMED';
    case partialReversed = 'PARTIAL_REVERSED';
    case reversed = 'REVERSED';
    case partialRefunded = 'PARTIAL_REFUNDED';
    case refunded = 'REFUNDED';
    case rejected = 'REJECTED';
}
