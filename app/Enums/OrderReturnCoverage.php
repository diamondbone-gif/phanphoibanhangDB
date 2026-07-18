<?php

namespace App\Enums;

enum OrderReturnCoverage: string
{
    case None = 'none';
    case Partial = 'partial';
    case Full = 'full';
}
