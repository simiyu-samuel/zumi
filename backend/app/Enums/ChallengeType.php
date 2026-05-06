<?php

namespace App\Enums;

enum ChallengeType: string
{
    case Direct = 'direct';
    case Open = 'open';
    case Wave = 'wave';
}
