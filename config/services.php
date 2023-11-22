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
        'senders' => [
            'moscow' => 14329,
        ],
    ],

    'inSales' => [
        'login' => env('INSALES_LOGIN'),
        'password' => env('INSALES_PASSWORD'),
    ],

    'moySklad' => [
        'token' => env('MOY_SKLAD_TOKEN'),
    ],

];
