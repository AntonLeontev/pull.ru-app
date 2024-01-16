<?php

namespace App\Services\Tinkoff\Enums;

enum PaymentObject: string
{
    /**
     * товар
     */
    case commodity = 'commodity';
    /**
     * подакцизный товар
     */
    case excise = 'excise';
    /**
     * работа
     */
    case job = 'job';
    /**
     * услуга
     */
    case service = 'service';
    /**
     * платеж
     */
    case payment = 'payment';
    /**
     * агентское вознаграждение
     */
    case agent_commission = 'agent_commission';
    /**
     * составной предмет расчета
     */
    case composite = 'composite';
    /**
     * иной предмет расчета
     */
    case another = 'another';
}
