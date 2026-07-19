<?php

namespace App\Enums;

enum FinancialTransactionState: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
