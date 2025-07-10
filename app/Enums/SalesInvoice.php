<?php

namespace App\Enums;

class SalesInvoice extends BaseEnum
{
    const PENDING = '1';
    const APPROVED = '2';
    const REJECTED = '3';
    const MAIL_SEND = '4';
    const DELETED_BY_ADMIN = '1';
}
