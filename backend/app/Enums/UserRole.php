<?php

namespace App\Enums;

enum UserRole: string
{
    case User    = 'user';
    case Pro     = 'pro';
    case Studio  = 'studio';
    case Admin   = 'admin';
}
