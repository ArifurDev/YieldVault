<?php

namespace App\Enum\Task;

enum MediaType : string
{
    case VIDEO = 'video';
    case IMAGE = 'image';
    case URL   = 'url';
    case NONE  = 'none';
}
