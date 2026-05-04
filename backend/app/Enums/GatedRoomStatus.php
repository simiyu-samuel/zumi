<?php

namespace App\Enums;

enum GatedRoomStatus: string
{
    case Scheduled = 'scheduled';
    case Live      = 'live';
    case Ended     = 'ended';
    case Cancelled = 'cancelled';
}
