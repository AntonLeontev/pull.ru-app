<?php

namespace Src\Domain\InSales\Enums;

enum WebhookTopic: string
{
    case ordersCreate = 'orders/create';
    case ordersUpdate = 'orders/update';
    case ordersDestroy = 'orders/destroy';
    case productsCreate = 'products/create';
    case productsUpdate = 'products/update';
    case clientCreate = 'client/create';
    case clientUpdate = 'client/update';
}
