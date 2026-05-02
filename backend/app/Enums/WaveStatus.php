<?php

namespace App\Enums;

enum WaveStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Ready      = 'ready';
    case Failed     = 'failed';
}
