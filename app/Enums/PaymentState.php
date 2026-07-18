<?php

namespace App\Enums;

enum PaymentState: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
    case Failed = 'failed';
}
