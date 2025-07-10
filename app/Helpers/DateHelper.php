<?php

namespace App\Helpers;

use DateTime;
use DateTimeZone;

class DateHelper
{
    // Leads
    const LEAD_DATE_FORMAT = 'd/m/Y H:i';
    const LEAD_DATE_FORMAT_MYSQL = '%d/%m/%Y %H:%i';

    // Client
    const CLIENT_DATE_FORMAT = 'd/m/Y H:i';
    const CLIENT_DATE_FORMAT_MYSQL = 'd/m/Y H:i';

    // Users
    const USER_CREATED_DATE = 'd/m/Y H:i';
    const USER_CREATED_DATE_MYSQL = '%d/%m/%Y %H:%i';
    const DOB_DATE_FORMAT = 'd/m/Y';

    // Invoices
    const INVOICE_PAID_AT = 'd/m/Y';
    const INVOICE_LIST_CREATED_AT = 'd/m/Y';
    const INVOICE_LIST_CREATED_AT_MYSQL = '%d/%m/%Y';
    const INVOICE_LIST_DUE_DATE = 'd/m/Y';
    const INVOICE_LIST_DUE_DATE_MYSQL = '%d/%m/%Y';
    const INVOICE_EXPORT_INVOICE_DATE = 'd/m/Y';
    const INVOICE_EXPORT_DUE_DATE = 'd/m/Y';
    const INVOICE_NOTE_CREATED_AT = 'd/m/Y H:i';

    const NOTE_REMINDER_CREATED_AT = 'd/m/Y H:i';

    // Expenses
    const EXPENSE_DATE = 'd/m/Y';
    const EXPENSE_DATE_MYSQL = '%d/%m/%Y';
    const EXPENSE_REMIND_AT = 'd/m/Y';
    const EXPENSE_REMIND_AT_MYSQL = '%d/%m/%Y';
    const EXPENSE_NOTE_CREATED_AT = 'd/m/Y H:i';

    // Follow ups
    const FOLLOW_UP_DATE = 'd/m/Y h:i A';
    const FOLLOW_UP_DATE_DATE = 'd/m/Y';
    const FOLLOW_UP_DATE_HOUR = 'h:i A';
    const FOLLOW_UP_DATE_MYSQL = '%d/%m/%Y %h:%i %p';
    const FOLLOW_UP_REMIND_AT = 'd/m/Y g:i A';
    const FOLLOW_UP_REMIND_AT_MYSQL = '%d/%m/%Y %h:%i %p';
    const FOLLOW_UP_CREATED = 'd/m/Y g:i A';
    const FOLLOW_UP_CREATED_MYSQL = '%d/%m/%Y %h:%i %p';

    public static function getGmtOffsetFromTimezone(string $timezone)
    {
        if ($timezone != '') {
            $date = new DateTime('now', new DateTimeZone($timezone));
            $offset = $date->getOffset() / 3600;
            $hours = floor($offset);
            $minutes = ($offset - $hours) * 60;
            $offset_string = sprintf('%+03d:%02d', $hours, $minutes);
            return $offset_string;
        }
        return '';
    }
}
