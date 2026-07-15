<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'BarFlow'),
    'env' => env('APP_ENV', 'local'),
    'debug' => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOL),
    'base_url' => env('APP_URL', 'http://localhost/barflow/public'),
    'timezone' => env('APP_TIMEZONE', 'Africa/Douala'),
    'session_timeout_minutes' => (int) env('SESSION_TIMEOUT_MINUTES', '120'),
    'remember_me_days' => (int) env('REMEMBER_ME_DAYS', '15'),
];
