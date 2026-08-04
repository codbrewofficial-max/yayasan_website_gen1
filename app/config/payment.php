<?php

return [
    /*
     |-----------------------------------------------------------------------
     | Midtrans
     |-----------------------------------------------------------------------
     | Konfigurasi payment gateway. Diisi via .env (per lingkungan).
    */
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'base_url' => env('MIDTRANS_BASE_URL', 'https://app.sandbox.midtrans.com/snap/v1'),
        'expiry' => [
            'duration' => (int) env('MIDTRANS_EXPIRY_DURATION', 30),
            'unit' => env('MIDTRANS_EXPIRY_UNIT', 'minutes'),
        ],
    ],
];