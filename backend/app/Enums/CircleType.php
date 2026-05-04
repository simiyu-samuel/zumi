<?php

namespace App\Enums;

enum CircleType: string
{
    case Public = 'public';
    case FreeGated = 'free_gated';
    case Premium = 'premium';
}
