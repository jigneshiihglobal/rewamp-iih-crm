<?php

return [
    'workspace' => env('APP_WORKSPACE', 1),
    'app_server' => env('APP_SERVER', 'true'),
    'invoice_prefix' => 'INV',
    'invoice_number_starts_from' => 5001,
    'credit_note_prefix' => 'CRE',
    'credit_note_number_starts_from' => "001",
    'statistics_currency' => env('APP_STATISTICS_CURRENCY', 'GBP'),
    'gbpconvert_access_key' => env('APP_GBPCONVERT_ACCESS_KEY', 'ccd018846ba511618644fb55f531c384'),
    'invoice_dashboard_currency' => env('APP_INVOICE_DASHBOARD_CURRENCY', 'GBP'),
    'invoices_types' => [
        'one-off' => '0',
        'subscription' => '1',
    ],
    'subscription_types' => [
        'monthly' => '0',
        'yearly' => '1',
    ],
    'cron_email_recipients' => [
        'error_mail_address' => ['ashish.php.iih@gmail.com'],
        'upcoming_expenses' => explode(',', env('EXPENSE_REMINDER_RECIPIENT', 'sanjay@iihglobal.com')),
    ],
    'database' => [
        'backup' => [
            'recipient' => explode(',', env('DB_BACKUP_RECIPIENT', 'bhavesh@iihglobal.com')),
            'duration' => env('DB_BACKUP_DURATION', 15),
        ]
    ],
    'system_settings' => [
        'default' => [
            'whitelisted_ips' => ["122.170.107.160"],
            'login_mail_recipients' => ["bhavesh@iihglobal.com"],
        ],
        'mail_timezone' => 'Asia/Kolkata',
    ],
    'payment_reminder' => [
        'interval_in_days' => 7
    ],
    'mail' => [
        'from' => [
            'name' => [
                'db_backup' => 'IIH CRM - Database',
                'login_info' => 'IIH CRM - Login',
                'set_password' => 'IIH CRM',
                'sub_cron_error' => 'IIH CRM - CRON Error',
                'leads' => 'IIH CRM - Lead Assigned',
                'upcoming_expense_reminder' => 'IIH CRM - Expenses',
                'follow_up_reminder' => 'IIH CRM - Follow Up',
                'invoice_mail_error' => 'IIH CRM - Invoice Mail Error',
            ],
        ],
    ],
    'bank_company_detail_map' =>  [
        [
            'bank_id' => 5,
            'company_detail_id' => 2,
        ],
    ],
    'hrms_customer_project' => [
        'auth_token' => env('AUTH_TOKEN'),
        'hrms_url' => env('HRMS_URL'),
    ],
    'wp_review_url' => env('WP_REVIEW_URL', 'https://www.iihglobal.com/feedback-form/'),
];
