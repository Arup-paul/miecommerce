<?php

return [
    'base_url' => env('MIACCOUNTS_BASE_URL', 'http://127.0.0.1:8000'),
    'api_key' => env('MIACCOUNTS_API_KEY', ''),
    'enabled' => env('MIACCOUNTS_ENABLED', false),
    'timeout' => env('MIACCOUNTS_TIMEOUT', 5),
    'inbound_secret' => env('MIACCOUNTS_INBOUND_SECRET', ''),
];
