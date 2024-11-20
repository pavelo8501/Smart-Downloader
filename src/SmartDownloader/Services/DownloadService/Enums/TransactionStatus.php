<?php

namespace SmartDownloader\Services\DownloadService\Enums;

enum TransactionStatus: int
{
    case UNINITIALIZED  = 0;
    case IN_PROGRESS  = 1;
    case COMPLETE = 2;
    case FAILED = 3;
}
