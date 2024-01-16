<?php

namespace Src\Domain\Synchronizer\Enums;

enum OrderPaymentStatus: string
{
    case pending = 'pending';
    case paid = 'paid';
}
