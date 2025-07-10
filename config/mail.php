<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    'accounts_mail_bcc' => explode(',', env('ACCOUNTS_MAIL_BCC', '')),
    'accounts_mail_mailer' => env('ACCOUNTS_MAIL_MAILER', 'accounts_smtp'),
    'accounts_mail_from' => [
        'address' => env('ACCOUNTS_MAIL_FROM_ADDRESS'),
        'name' => env('ACCOUNTS_MAIL_FROM_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses",
    |            "postmark", "log", "array", "failover"
    |
    */

    'mailers' => [

        'accounts_smtp' => [
            'transport' => 'smtp',
            'host' => env('ACCOUNTS_MAIL_HOST'),
            'port' => env('ACCOUNTS_MAIL_PORT'),
            'encryption' => env('ACCOUNTS_MAIL_ENCRYPTION'),
            'username' => env('ACCOUNTS_MAIL_USERNAME'),
            'password' => env('ACCOUNTS_MAIL_PASSWORD'),
        ],

        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -t -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
        'welcome_mail' => env('WELCOME_FROM_MAIL', 'hello@iihglobal.com'),
    ],

    'wise_payment' => [
        'to_address' => env("WISE_PAYMENT_TO_MAIL",'sanjay@iihglobal.com'),
        'bcc_address' => env("WISE_PAYMENT_BCC_MAIL",'ashish.php.iih@gmail.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

    'feedback_received_mail_address' => env('FEEDBACK_RECEIVED_MAIL_ADDRESS', 'ashish.php.iih@gmail.com'),
    'sales_invoice_sent_mail_address' => env('SALES_INVOICE_SENT_MAIL_ADDRESS', 'ashish.php.iih@gmail.com'),

];
