<?php

namespace App\Enum\Task;

enum Type : string
{
    case WATCH_AD   = 'watch_ad';
    case UPLOAD     = 'upload';
    case CLICK      = 'click';
    case SHARE      = 'share';
    case INVITE     = 'invite';
}
