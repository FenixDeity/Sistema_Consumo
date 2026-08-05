<?php

use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'Sistema Consumo'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'America/Mexico_City',
    'locale' => env('APP_LOCALE', 'es'),
    'fallback_locale' => 'es',
    'faker_locale' => 'es_MX',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
    ])->toArray(),
];
