<?php

return [
    'queue' => 'campaigns',

    'rate_limits' => [
        'email_per_min' => 60,
        'whatsapp_per_min' => 30,
    ],

    'automation' => [
        'window_tolerance_minutes' => 10,
        'default_timezone' => 'America/Campo_Grande',
        'inactive_days' => 60,
    ],

    'channels' => [
        'supported' => ['email', 'whatsapp'],

        'email' => [
            // Tenant email channel is available only when tenancy SMTP is fully configured.
            'required_settings' => [
                'host',
                'port',
                'username',
                'password',
                'from_address',
            ],
        ],

        'whatsapp' => [
            'providers' => [
                'whatsapp_business' => [
                    'required_settings' => [
                        'meta_access_token',
                        'meta_phone_number_id',
                    ],
                ],
                'zapi' => [
                    'required_settings' => [
                        'zapi_api_url',
                        'zapi_token',
                        'zapi_client_token',
                        'zapi_instance_id',
                    ],
                ],
                'waha' => [
                    'required_settings' => [
                        'waha_base_url',
                        'waha_api_key',
                        'waha_session',
                    ],
                ],
            ],

            // Legacy tenant API fallback supported by existing WhatsApp sender.
            'legacy_required_settings' => [
                'api_url',
                'api_token',
            ],
        ],
    ],
];
