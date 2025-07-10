<?php

return [
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
    'webhook_key' => env('STRIPE_WEBHOOK_SECRET'),

    'shalin_secret_key' => env('SHALIN_STRIPE_SECRET_KEY'),
    'shalin_publishable_key' => env('SHALIN_STRIPE_PUBLISHABLE_KEY'),
    'shalin_webhook_key' => env('SHALIN_STRIPE_WEBHOOK_SECRET'),
];
