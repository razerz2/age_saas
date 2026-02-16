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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'asaas' => [
        'api_key' => env('ASAAS_API_KEY'),
        'url' => env('ASAAS_API_URL', 'https://sandbox.asaas.com/api/v3/'),
        'webhook_secret' => env('ASAAS_WEBHOOK_SECRET'),
    ],

    'whatsapp' => [
        // Provedor a ser usado: 'whatsapp_business' ou 'zapi'
        'provider' => env('WHATSAPP_PROVIDER', 'whatsapp_business'),

        // Configurações para WhatsApp Business API
        'business' => [
            'api_url' => env('WHATSAPP_BUSINESS_API_URL', 'https://graph.facebook.com/v18.0'),
            'token' => env('WHATSAPP_BUSINESS_TOKEN'),
            'phone_id' => env('WHATSAPP_BUSINESS_PHONE_ID'),
        ],

        // Configurações para Z-API
        'zapi' => [
            'api_url' => env('ZAPI_API_URL', 'https://api.z-api.io'),
            'token' => env('ZAPI_TOKEN'), // Token da instância (usado na URL)
            'client_token' => env('ZAPI_CLIENT_TOKEN'), // Client-Token de segurança da conta (usado no header)
            'instance_id' => env('ZAPI_INSTANCE_ID'),
        ],

        // Configurações para WAHA
        'waha' => [
            'base_url' => env('WAHA_BASE_URL'),
            'api_key' => env('WAHA_API_KEY'),
            'session' => env('WAHA_SESSION', 'default'),
        ],

        // Configurações legadas (mantidas para compatibilidade)
        'api_url' => env('WHATSAPP_API_URL'),
        'token' => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],

];
