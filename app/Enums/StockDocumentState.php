<?php

namespace App\Enums;

enum StockDocumentState: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Posted = 'posted';
    case Cancelled = 'cancelled';
}
