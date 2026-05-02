<?php

namespace App\Enums;

enum WaveVisibility: string
{
    case Public    = 'public';
    case Followers = 'followers';
    case Circle    = 'circle';
    case Gated     = 'gated';
}
