<?php

namespace App\Enums;

enum CircleMemberRole: string
{
    case Owner = 'owner';
    case Moderator = 'moderator';
    case Member = 'member';
}
