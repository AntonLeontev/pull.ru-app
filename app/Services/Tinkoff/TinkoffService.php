<?php

namespace App\Services\Tinkoff;

use App\Services\Tinkoff\Entities\ReceiptItem;
use App\Services\Tinkoff\Enums\PaymentObject;

class TinkoffService
{
    public function __construct(public TinkoffApi $api)
    {
    }

    public function init(object $request)
    {
        $items = [];

        foreach ($request->order_lines as $line) {
            $items[] = new ReceiptItem(
                $line->title,
                $line->sale_price * 100,
                $line->quantity,
                $line->sale_price * 100 * $line->quantity,
                PaymentObject::commodity
            );
        }

        if ($request->delivery_price > 0) {
            $items[] = new ReceiptItem(
                'Доставка',
                $request->delivery_price * 100,
                1,
                $request->delivery_price * 100,
                PaymentObject::service
            );
        }

        $response = $this->api->init(
            $request->total_price * 100,
            $request->number,
            $request->client->email,
            $items
        );

        return $response;
    }
}
