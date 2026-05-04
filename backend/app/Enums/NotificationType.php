<?php

namespace App\Enums;

enum NotificationType: string
{
    case Mention = 'mention';
    case Follower = 'follower';
    case CircleRequest = 'circle_request';
    case CircleApproved = 'circle_approved';
    case WaveLiked = 'wave_liked';
    case NewComment = 'new_comment';
}
