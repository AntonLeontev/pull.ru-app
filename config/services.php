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
        'shop' => 200422,
        'warehouse' => 12186,
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
    ],

    'inSales' => [
        'login' => env('INSALES_LOGIN'),
        'password' => env('INSALES_PASSWORD'),
    ],

    'moySklad' => [
        'token' => env('MOY_SKLAD_TOKEN'),
        // 'default_price_type_id' => '5a0279d3-8857-11ee-0a80-0d4e00501cd0', // aner
        'default_price_type_id' => '3f7ac9c1-ad4b-11ee-0a80-0dfd005ae996', // pull
        // 'organization' => '5a009435-8857-11ee-0a80-0d4e00501cc8', // aner
        'organization' => '3f775ef7-ad4b-11ee-0a80-0dfd005ae98d', // pull
        // 'store' => '5a0185cd-8857-11ee-0a80-0d4e00501cca', // aner
        'store' => '3f79e955-ad4b-11ee-0a80-0dfd005ae990', // pull
        'enabled' => env('SYNC_MOY_SKLAD'),
    ],

];
