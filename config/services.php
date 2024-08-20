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
        'brand_property_id' => 53902709,
    ],

    'moySklad' => [
        'token' => env('MOY_SKLAD_TOKEN'),
        'default_price_type_id' => '3f7ac9c1-ad4b-11ee-0a80-0dfd005ae996', // pull
        'organization' => '3f775ef7-ad4b-11ee-0a80-0dfd005ae98d', // pull
        'store' => 'fb225577-cf32-11ee-0a80-141200432325', // pull
        'price_id' => '3f7ac9c1-ad4b-11ee-0a80-0dfd005ae996',
        'old_price_id' => 'f5850725-cef7-11ee-0a80-0f3f003357ed',
        'default_customer_id' => '40044568-ad4b-11ee-0a80-0dfd005ae9ae',
        'enabled' => env('SYNC_MOY_SKLAD'),
        'fields_to_update' => [
            'salePrices',
            'buyPrices',
            'Старая цена',
        ],
    ],

    'cloudpayments' => [
        'public_id' => env('CLOUDPAYMENTS_ID'),
        'password' => env('CLOUDPAYMENTS_PASSWORD'),
        'inn' => '7203567990',
    ],

    'planfact' => [
        'api_key' => env('PLANFACT_API_KEY'),
        'accounts' => [
            'cdek' => 623684,
        ],
        'operation_categories' => [
            'income' => [
                'delivery' => 7809642,
                'men' => 7805725,
                'women' => 7805728,
            ],
            'outcome' => [
                'return' => 7806987, // Возврат на складе ФФ
                'delivery_return_orders' => 7806218, // Доставка возвратных заказов
                'delivery_direct_orders' => 7806214, // Доставка прямых заказов
                'acceptance' => 7806990, // приемка
                'assembly' => 7806985, // сборка
                'warehouse_insurance' => 7806989, // страховка
                'storage' => 7806988, // хранение
            ],
        ],
        'projects' => [
            'limmite' => 998968,
        ],
    ],

    'telegram' => [
        'limmite_bot_token' => env('TELEGRAM_LIMMITE_BOT_TOKEN'),
        'limmite_notifications' => [
            'chat' => env('TELEGRAM_NOTIFICATION_CHAT_ID'),
            'thread' => env('TELEGRAM_NOTIFICATION_THREAD'),
        ],
        'limmite_logs' => [
            'chat' => env('TELEGRAM_SYNC_CHAT_ID'),
            'thread' => env('TELEGRAM_SYNC_THREAD'),
        ],
    ],

    'ip2location' => [
        'key' => env('IP2LOCATION_KEY'),
    ],

    'unisender' => [
        'key' => env('UNISENDER_KEY'),
    ],

    'dicards' => [
        'api_key' => env('DICARDS_API_KEY'),
        'api_id' => env('DICARDS_API_ID'),
    ],
];
