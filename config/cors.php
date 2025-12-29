<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Cross-Origin Resource Sharing (CORS) Configuration
     * |--------------------------------------------------------------------------
     * |
     * | Here you may configure your settings for cross-origin resource sharing
     * | or "CORS". This determines what cross-origin operations may execute
     * | in web browsers. You are free to adjust these settings as needed.
     * |
     * | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
     * |
     */
    'paths' => [
        'api/*',
        'login',
        'logout',
        'sanctum/csrf-cookie'
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // Temporarily allow all origins for testing
    // 'allowed_origins' => [
    //     'http://localhost:3000',
    //     'http://localhost:3001',
    //     'http://localhost:3002',
    //     'http://127.0.0.1:3000',
    //     'http://127.0.0.1:3001',
    //     'http://127.0.0.1:3002',
    //     'https://dms-redevabilite.dev',
    //     'https://ug.dms-redevabilite.dev',
    //     'https://organisation.dms-redevabilite.dev',
    //     'https://admin.dms-redevabilite.dev',
    //     'https://dms-redevabilite.com',
    //     'https://ug.dms-redevabilite.com',
    //     'https://organisation.dms-redevabilite.com',
    //     'https://admin.dms-redevabilite.com',
    //     'http://192.168.8.102:3000',
    //     'http://192.168.8.102:3001',
    //     'http://192.168.8.102:3002',
    //     'http://192.168.8.105:3000',
    //     'http://192.168.8.105:3001',
    //     'http://192.168.8.105:3002',
    // ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
