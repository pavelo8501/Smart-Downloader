<?php


namespace SmartDownloader\Services\ListenerService\Models;

use Exception;
use SmartDownloader\Models\Download;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\ModelInterfaces\TransactionDataContainer;

class DataContainer implements TransactionDataContainer
{

    private array $records = [];

    public function __construct(public string $itemType, callable $convertFn)
    {
        if (!class_exists($itemType)) {
            throw new Exception("Invalid type: $itemType does not exist.");
        }
    }

    private function conversion(object $download, callable $converter): TransactionDataClass | null
    {
        $result = $converter($download);
        if ($result instanceof TransactionDataClass) {
            return $result;
        }
        return null;
    }

    function newRecord(object $download, callable $converter): TransactionDataClass | null
    {
        $transaction  = $this->conversion($download, $converter);
        $this->records[] = $transaction;
        return $transaction;
    }
}
