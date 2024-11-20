<?php

namespace SmartDownloader\Services\ListenerService\Enums;

enum ListenerTasks: int {
    case DOWNLOAD_STARTED  = 1;
    case DOWNLOAD_PAUSED   = 2;
    case DOWNLOAD_RESUMED  = 3;
    case DOWNLOAD_CANCELLED = 4;
}