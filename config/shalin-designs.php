<?php

return [
    'accounts_mail' => [
        'from' => [
            'address' => env(
                'SHALIN_ACCOUNT_MAIL_FROM_ADDRESS',
                'accounts@shalindesigns.com'
            ),
            'name' => env(
                'SHALIN_ACCOUNT_MAIL_FROM_NAME',
                'Shalin Designs Account'
            ),
        ],
        'bcc'  => [
            env("SHALIN_BCC_MAIL_ADDRESS",'sanjay@shalindesigns.com')
        ],
    ],
    'mail_from_emails' =>  [
        'leads' => env("SHALIN_BCC_MAIL_ADDRESS",'sanjay@shalindesigns.com'),
        'follow_up_reminder' =>  env("SHALIN_BCC_MAIL_ADDRESS",'sanjay@shalindesigns.com'),
        'upcoming_expense_reminder' => env("SHALIN_BCC_MAIL_ADDRESS",'sanjay@shalindesigns.com'),
    ],
    'mail_from_names' =>  [
        'leads' => 'Shalin Designs - Lead Assigned',
        'follow_up_reminder' =>  'Shalin Designs - Follow Up',
        'upcoming_expense_reminder' => 'Shalin Designs - Expenses',
    ],
    'follow_ups'  => [
        'client_emails' => [
            'bcc' => [
                env("SHALIN_BCC_MAIL_ADDRESS",'sanjay@shalindesigns.com'),
            ],
        ],
    ],
];
