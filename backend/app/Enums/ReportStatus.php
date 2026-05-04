<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';
}
