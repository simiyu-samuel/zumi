<?php

namespace App\Enums;

enum ModerationAction: string
{
    case None = 'none';
    case DeleteContent = 'delete_content';
    case BanUser = 'ban_user';
    case WarnUser = 'warn_user';
}
