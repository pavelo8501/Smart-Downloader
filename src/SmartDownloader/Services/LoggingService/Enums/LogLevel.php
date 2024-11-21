<?php

namespace SmartDownloader\Services\LoggingService\Enums;

enum LogLevel: int {
    case MESSAGE  = 1;
    case EVENT   = 2;
    case WARNING  = 3;
    case HANDLED_EXCEPTION = 4;
}
