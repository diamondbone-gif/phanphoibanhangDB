<?php

namespace App\Enums;

enum FinancialTransactionType: string
{
    case Receipt = 'receipt';
    case Refund = 'refund';
    case Deposit = 'deposit';
    case Adjustment = 'adjustment';
}
