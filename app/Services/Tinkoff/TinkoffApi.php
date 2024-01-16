<?php

namespace App\Services\Tinkoff;

use App\Services\Tinkoff\Exceptions\TinkoffApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TinkoffApi
{
    public static function init(
        int $amount,
        int $orderId,
        string $customerEmail,
        array $items,
    ): Response {
        $data = [
            'TerminalKey' => config('services.tinkoff.terminal'),
            'Amount' => $amount,
            'OrderId' => $orderId,
            'PayType' => 'O',
            'NotificationURL' => config('services.tinkoff.notification_url'),
            'SuccessURL' => config('services.tinkoff.success_url'),
            'FailURL' => config('services.tinkoff.fail_url'),
            'Receipt' => [
                'Taxation' => config('services.tinkoff.taxation'),
                'Email' => $customerEmail,
                'Items' => $items,
            ],
        ];

        $data['Token'] = self::makeToken($data);

        $response = Http::tinkoff()->post('/Init', $data);

        if ($response->json('Success') === false) {
            throw new TinkoffApiException($response);
        }

        return $response;
    }

    private static function makeToken(array $data): string
    {
        $tokenCredentials = collect();

        foreach ($data as $key => $value) {
            if (is_null($value) || is_array($value)) {
                continue;
            }

            $tokenCredentials->put($key, $value);
        }

        $string = $tokenCredentials->put('Password', config('services.tinkoff.password'))
            ->sortKeys()
            ->join('');

        return hash('sha256', $string);
    }
}
