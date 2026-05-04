<?php

namespace App\Enums;

enum CircleType: string
{
    case Public = 'public';
    case Private = 'private'; // Approval required
    case Gated = 'gated';     // Paid Drops subscription
}
