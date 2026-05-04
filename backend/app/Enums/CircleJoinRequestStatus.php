<?php

namespace App\Enums;

enum CircleJoinRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
}
