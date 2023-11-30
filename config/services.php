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

    'cdek' => [
        'login' => env('CDEK_LOGIN'),
        'password' => env('CDEK_PASSWORD'),
        'shop' => 200422,
        'warehouse' => 12186,
        'senders' => [
            'moscow' => 14329,
        ],
        'enabled' => env('SYNC_CDEK'),
    ],

    'inSales' => [
        'login' => env('INSALES_LOGIN'),
        'password' => env('INSALES_PASSWORD'),
    ],

    'moySklad' => [
        'token' => env('MOY_SKLAD_TOKEN'),
        'default_price_type_id' => '5a0279d3-8857-11ee-0a80-0d4e00501cd0',
        'uom' => [
            'pce' => '19f1edc0-fc42-4001-94cb-c9ec9c62ec10',
        ],
        'organization' => '5a009435-8857-11ee-0a80-0d4e00501cc8',
        'enabled' => env('SYNC_MOY_SKLAD'),
    ],

];
