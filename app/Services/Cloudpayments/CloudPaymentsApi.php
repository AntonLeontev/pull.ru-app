<?php

namespace App\Services\Cloudpayments;

use App\Services\Cloudpayments\Entities\CustomerReceipt;
use App\Services\Cloudpayments\Enums\Type;
use App\Services\Cloudpayments\Exceptions\CloudPaymentsApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CloudPaymentsApi
{
    /**
     * Метод формирования кассового чека
     *
     * @param  string  $inn  ИНН вашей организации или ИП, на который зарегистрирована касса
     * @param  Type  $type  Признак расчета
     * @param  CustomerReceipt  $customerReceipt  Состав чека
     * @param  string|null  $invoiceId  Номер заказа в вашей системе
     * @param  string|null  $accountId  Идентификатор пользователя в вашей системе
     */
    public static function receipt(
        string $inn,
        Type $type,
        CustomerReceipt $customerReceipt,
        ?string $invoiceId = null,
        ?string $accountId = null,
    ): Response {
        $response = Http::cloudpayments()
            ->post('/kkt/receipt', [
                'Inn' => $inn,
                'Type' => $type->value,
                'CustomerReceipt' => $customerReceipt,
                'InvoiceId' => $invoiceId,
                'AccountId' => $accountId,
            ]);

        if ($response->json('Success') === false) {
            throw new CloudPaymentsApiException($response);
        }

        return $response;
    }

    public static function test(): Response
    {
        return Http::cloudpayments()->post('test');
    }
}
