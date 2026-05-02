<?php

namespace App\Enums;

enum DropsTransactionDirection: string
{
    case Credit = 'credit';
    case Debit  = 'debit';
}
