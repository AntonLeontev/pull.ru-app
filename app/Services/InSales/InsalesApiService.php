<?php

namespace App\Services\InSales;

use Illuminate\Http\Client\Response;
use Src\Domain\Synchronizer\Enums\OrderStatus;

class InsalesApiService
{
    public static function updateCdekTraceInOrder(int $insalesOrderId, int $cdekTrace): Response
    {
        return InSalesApi::updateOrder($insalesOrderId, [
            'order' => [
                'fields_values_attributes' => [
                    [
                        'handle' => 'trace',
                        'value' => "https://www.cdek.ru/ru/tracking/?order_id=$cdekTrace",
                    ],
                ],
            ],
        ]);
    }

    public static function updateKeepFreeDateInOrder(int $insalesOrderId, string $value): Response
    {
        return InSalesApi::updateOrder($insalesOrderId, [
            'order' => [
                'fields_values_attributes' => [
                    [
                        'handle' => 'keep_untill',
                        'value' => $value,
                    ],
                ],
            ],
        ]);
    }

    public static function updateOrderState(int $id, OrderStatus $status): Response
    {
        return InSalesApi::updateOrder($id, [
            'order' => [
                'custom_status_permalink' => $status->toInsales(),
            ],
        ]);
    }

    public static function updateOrderPaymentState(int $id, string $state): Response
    {
        return InSalesApi::updateOrder($id, [
            'order' => [
                'financial_status' => $state,
            ],
        ]);
    }
}
