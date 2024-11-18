<?php

namespace SmartDownloader\Services\ListenerService\ModelInterfaces;

use SmartDownloader\Models\Download;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

interface TransactionDataContainer{
    /**
    * @template TDownload
    * @template TTransactionData
    * @param TDownload $download
    * @param callable $converter
    */
    function registerNewConnection(DownloadRequest $download, DownloadDataClass $downloadData):void;


}