<?php

namespace SmartDownloader\Services\DownloadService\Enums;

enum TransactionStatus: int{
    case UNINITIALIZED  = 0;
    case IN_PROGRESS  = 1;
    case SUSPENDED = 2;
    case COMPLETE = 3;
    case FAILED = 4;
    case CORRUPTED = 5;
}
