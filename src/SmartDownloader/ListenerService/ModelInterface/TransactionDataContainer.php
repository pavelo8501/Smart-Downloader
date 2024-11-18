<?php

namespace SmartDownloader\Services\ListenerService\ModelInterfaces;

use SmartDownloader\Models\Download;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

interface TransactionDataContainer
{
/**
* @template TDownload
* @template TTransactionData
* @param TDownload $download
* @param callable $converter
* @return TTransactionData|null
*/
function newRecord(object $download, callable $converter):TransactionDataClass|null;

}