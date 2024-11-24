<?php

namespace SmartDownloader\Services\ListenerService\Enums;

enum  ListenerTasks: int {
    case ON_START  = 1;
    case ON_PAUSE   = 2;
    case ON_RESUME  = 3;
    case ON_CANCEL = 4;
}