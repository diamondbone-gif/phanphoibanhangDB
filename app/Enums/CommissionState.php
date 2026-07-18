<?php

namespace App\Enums;

enum CommissionState: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Clawback = 'clawback';
}
