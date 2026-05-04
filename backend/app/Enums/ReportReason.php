<?php

namespace App\Enums;

enum ReportReason: string
{
    case Spam = 'spam';
    case Harassment = 'harassment';
    case InappropriateContent = 'inappropriate_content';
    case HateSpeech = 'hate_speech';
    case IntellectualProperty = 'intellectual_property';
    case Other = 'other';
}
