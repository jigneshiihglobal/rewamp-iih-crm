<?php

return [
    'transfer_statuses' => [
        "incoming_payment_waiting"      => "On its way to Wise",
        "incoming_payment_initiated"    => "On its way to Wise",
        "processing"                    => "Processing",
        "funds_converted"               => "Processing",
        "outgoing_payment_sent"         => "Sent",
        "charged_back"                  => "Charged back",
        "cancelled"                     => "Cancelled",
        "cancelled_refund_processing"   => "Refund in progress",
        "funds_refunded"                => "Refunded",
        "bounced_back"                  => "Bounced back",
        "unknown"                       => "Unknown",
    ],

    'fee_types' => [
        'ATM_WITHDRAWAL'    => 'Fee charged by Wise',
        'ATM_MACHINE'       => 'Fee charged by the ATM owner',
    ],

    'transaction_states' => [
        'IN_PROGRESS'   => 'The transaction is still in progress',
        'COMPLETED'     => 'The transaction is completed',
        'DECLINED'      => 'The transaction has been declined',
        'UNKNOWN'       => 'Default fallback status',
    ],

    'verification_states' => [
        'VERIFIED'              => 'Additional verification check has passed',
        'EVIDENCE_REQUIRED'     => 'Additional verification check has failed, evidences are required',
    ],

    'review_outcomes' => [
        'DOCUMENT_POOR_QUALITY'                     => 'Document is of poor quality',
        'DOCUMENT_MISSING_NAME'                     => 'Document is missing name',
        'DOCUMENT_MISSING_ISSUE_DATE'               => 'Document is missing issue date',
        'DOCUMENT_MISSING_COMPANY_LOGO_LETTERHEAD'  => 'Document is missing company logo or letterhead',
        'DOCUMENT_NOT_COMPLETE'                     => 'Document is partially cut-off and does not contain full information',
        'DOCUMENT_OUT_OF_DATE'                      => 'Document is out of date',
        'DOCUMENT_TYPE_UNACCEPTABLE'                => 'Document type is not acceptable',
        'INVOICE_UNACCEPTABLE'                      => 'Document type is not acceptable',
        'PHOTO_ID_UNACCEPTABLE'                     => 'Photo ID is not acceptable',
        'TRANSACTION_UNACCEPTABLE'                  => 'Screenshot of a transaction is not acceptable',
        'OTHER'                                     => 'The review did not pass for unknown reason',
    ],

    'card_statuses' => [
        'ACTIVE'    => 'Card is active and can be used',
        'INACTIVE'  => 'Physical card has not been activated',
        'BLOCKED'   => 'Card is blocked and cannot be reversed back to any state',
        'FROZEN'    => 'Card is “blocked” but temporarily',
    ],

    'api_token' => env('WISE_API_TOKEN', ''),
    'profile_id' => env('WISE_PROFILE_ID', ''),
    'api_url' => env('WISE_API_URL', 'https://api.wise.com/v1'),

    'api_endpoints' => [
        'GET_TRANSFER_BY_ID' => 'https://api.transferwise.com/v1/transfers/{{transferId}}',
        'GET_RECIPIENT_ACCOUNT_BY_ID' => 'https://api.transferwise.com/v1/accounts/{{accountId}}',
    ],
];
