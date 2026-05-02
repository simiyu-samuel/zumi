<?php

namespace App\Enums;

enum DropsTransactionStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';
    case Failed    = 'failed';
    case Reversed  = 'reversed';
}
