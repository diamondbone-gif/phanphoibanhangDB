<?php

namespace App\Enums;

enum StockDocumentType: string
{
    case Receipt = 'receipt';
    case Issue = 'issue';
    case Transfer = 'transfer';
    case AdjustmentIncrease = 'adjustment_increase';
    case AdjustmentDecrease = 'adjustment_decrease';
    case Return = 'return';
}
