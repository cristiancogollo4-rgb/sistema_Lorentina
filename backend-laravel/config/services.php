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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'lorentina' => [
        'whatsapp_number' => env('LORENTINA_WHATSAPP_NUMBER', '573000000000'),
        'instagram_url' => env('LORENTINA_INSTAGRAM_URL', 'https://www.instagram.com/lorentina'),
        'facebook_url' => env('LORENTINA_FACEBOOK_URL', 'https://www.facebook.com/lorentina'),
        'tiktok_url' => env('LORENTINA_TIKTOK_URL', 'https://www.tiktok.com/@lorentina'),
        'email' => env('LORENTINA_CONTACT_EMAIL', 'ventas@lorentina.com'),
        'city' => env('LORENTINA_CITY', 'Bucaramanga, Colombia'),
    ],

    'mercado_pago' => [
        'enabled' => env('MERCADO_PAGO_ENABLED', false),
        'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
        'currency' => env('MERCADO_PAGO_CURRENCY', 'COP'),
        'preferences_url' => env('MERCADO_PAGO_PREFERENCES_URL', 'https://api.mercadopago.com/checkout/preferences'),
        'use_sandbox_init_point' => env('MERCADO_PAGO_USE_SANDBOX_INIT_POINT', true),
        'notification_url' => env('MERCADO_PAGO_NOTIFICATION_URL'),
        'verify_ssl' => env('MERCADO_PAGO_VERIFY_SSL', true),
    ],

];
