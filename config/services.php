<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'cdekff' => [
        'login' => env('CDEKFF_LOGIN'),
        'password' => env('CDEKFF_PASSWORD'),
        // 'shop' => 200422, // old
        'shop' => 205303, // new
        // 'warehouse' => 12186, // old
        'warehouse' => 14413, // new
        'senders' => [
            'moscow' => 14329,
        ],
        'enabled' => env('SYNC_CDEK'),
    ],

    'cdek' => [
        'login' => env('CDEK_LOGIN'),
        'password' => env('CDEK_PASSWORD'),
        'cities' => [
            'moscow' => 44,
        ],
        'shipment_point' => 'MSK2290',
        'base_url' => env('CDEK_BASE_URL'),
    ],

    'inSales' => [
        'login' => env('INSALES_LOGIN'),
        'password' => env('INSALES_PASSWORD'),
        'online_payment_gateway_id' => '5982078',
        'deliver_payment_gateway_id' => '5470555',
    ],

    'moySklad' => [
        'token' => env('MOY_SKLAD_TOKEN'),
        'default_price_type_id' => '3f7ac9c1-ad4b-11ee-0a80-0dfd005ae996', // pull
        'organization' => '3f775ef7-ad4b-11ee-0a80-0dfd005ae98d', // pull
        'store' => 'fb225577-cf32-11ee-0a80-141200432325', // pull
        'price_id' => '3f7ac9c1-ad4b-11ee-0a80-0dfd005ae996',
        'old_price_id' => 'f5850725-cef7-11ee-0a80-0f3f003357ed',
        'enabled' => env('SYNC_MOY_SKLAD'),
        'fields_to_update' => [
            'salePrices',
            'buyPrices',
            'Старая цена',
        ],
    ],

    'tinkoff' => [
        'merchant_id' => env('TINKOFF_MERCHANT_ID'),
        'terminal_id' => env('TINKOFF_TERMINAL_ID'),
        'terminal' => env('TINKOFF_TERMINAL'),
        'password' => env('TINKOFF_PASSWORD'),
        'notification_url' => env('APP_URL').'/webhooks/online-payments/tinkoff',
        'success_url' => null,
        'fail_url' => null,
        'taxation' => 'usn_income_outcome',
        'tax' => 'none', // НДС в чеке
    ],

    'cloudpayments' => [
        'public_id' => env('CLOUDPAYMENTS_ID'),
        'password' => env('CLOUDPAYMENTS_PASSWORD'),
        'inn' => '7203567990',
    ],
];
