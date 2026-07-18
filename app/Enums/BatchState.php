<?php

namespace App\Enums;

enum BatchState: string
{
    case Active = 'active';
    case NearExpired = 'near_expired';
    case Expired = 'expired';
    case SoldOut = 'sold_out';
    case Inactive = 'inactive';
}
