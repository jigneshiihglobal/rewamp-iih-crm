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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'url' => env('SLACK_PAYMENT_NOTIFICATION_URL')
    ],
    'slack_shalin_designs' => [
        'url' => env('SLACK_SHALIN_DESIGN_PAYMENT_NOTIFICATION_URL')
    ],
    'mapquest' => [
        'key' => env('MAPQUEST_KEY'),
    ],
    'more_trees' => [
        'key' => env('MORE_TREES_KEY'),
        'sender_name' => env('MORE_TREES_SENDER_NAME', 'IIH Global'),
        'account_code' => env('MORE_TREES_SENDER_ACCOUNT_CODE', '815E72'),
    ],
    'plaid' => [
        'client_id' => env('PLAID_CLIENT_ID'),
        'secret' => env('PLAID_SECRET'),
        'env' => env('PLAID_ENV', 'sandbox'),
    ],
    'quickbooks' => [
        'client_id' => env('QB_CLIENT_ID'),
        'client_secret' => env('QB_CLIENT_SECRET'),
        'redirect_uri' => env('QB_REDIRECT_URI'),
        'environment' => env('QB_ENV', 'sandbox'),
    ],
];
