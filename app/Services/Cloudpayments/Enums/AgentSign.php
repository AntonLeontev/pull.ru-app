<?php

namespace App\Services\Cloudpayments\Enums;

/**
 * Признак агента, Тег ОФД 1057, 1222.
 */
enum AgentSign: int
{
    case bankPaymentAgent = 0;
    case bankPaymentSubagent = 1;
    case paymentAgent = 2;
    case paymentSubagent = 3;
    case attorney = 4;
    case comissioner = 5;
    case agent = 6;
}
