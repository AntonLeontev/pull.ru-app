<?php

namespace App\Services\InSales;

use Illuminate\Http\Client\Response;

class InsalesApiService
{
    public static function updateCdekTraceInOrder(int $insalesOrderId, int $cdekTrace): Response
    {
        return InSalesApi::updateOrder($insalesOrderId, [
            'order' => [
                'fields_values_attributes' => [
                    [
                        'handle' => 'trace',
                        'value' => "https://lk.cdek.ru/order-history/trace?orderNumber=$cdekTrace",
                    ],
                ],
            ],
        ]);
    }

    public static function updateOrderState(int $id, string $permalink): Response
    {
        return InSalesApi::updateOrder($id, [
            'order' => [
                'custom_status_permalink' => $permalink,
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
