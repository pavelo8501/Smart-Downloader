<?php

namespace SmartDownloader\Services\ListenerService\Interfaces;

use SmartDownloader\Models\Download;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

interface TransactionDataContainer{
    /**
     * @template TDownload
     * @template TTransactionData
     * @param DownloadRequest $download
     * @param callable $converter
     */
    function registerNew(DownloadRequest $download): TransactionDataClass;


}