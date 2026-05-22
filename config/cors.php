<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Qui puoi configurare le impostazioni CORS per il tuo progetto Laravel.
    | In produzione, limita allowed_origins al solo dominio dell'app.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Usa APP_URL nel .env: in produzione deve essere 'https://kommunitynetwork.com'
    // (o il dominio cPanel esatto). Non lasciare localhost o * in produzione.
    // DEPLOY CHECKLIST: verificare che APP_URL nel .env cPanel sia il dominio HTTPS corretto.
    'allowed_origins' => [env('APP_URL', 'http://localhost')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
