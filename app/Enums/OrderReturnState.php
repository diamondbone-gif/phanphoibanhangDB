<?php

namespace App\Enums;

enum OrderReturnState: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Failed = 'failed';
}
