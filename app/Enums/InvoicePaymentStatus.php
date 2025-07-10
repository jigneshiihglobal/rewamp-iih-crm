<?php

namespace App\Enums;

class InvoicePaymentStatus extends BaseEnum
{
    const UNPAID = 'unpaid';
    const PARTIALLY_PAID = 'partially_paid';
    const PAID = 'paid';

    const ALL = 'all';
}
