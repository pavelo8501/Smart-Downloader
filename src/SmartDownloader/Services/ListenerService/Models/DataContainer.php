<?php


namespace SmartDownloader\Services\ListenerService\Models;

use Exception;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Util\Http\Downloader;
use SmartDownloader\Models\Download;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\ModelInterfaces\TransactionDataContainer;

use function PHPUnit\Framework\returnValue;

class DataContainer implements TransactionDataContainer{

    private array $records = [];

    public Callback $converter;
    public static $TransactionDataClass;

    public function __construct(string $TransactionDataClass, Callback $converter)
    {
       $this->converter = $converter;
       $this->TransactionDataClass = $TransactionDataClass;
    }

    private function conversion(DownloadRequest $download): TransactionDataClass | null{
        if( $this->converter != null){
            return null;
        }
        return null;
    }

    function registerNewConnection(DownloadRequest $download,DownloadDataClass $downloadData):void{
        $newTransaction = new TransactionDataClass();
        $newTransaction->url  =   $download->url;
        $newTransaction->path  =  $download->path;
        $this->records[] = $newTransaction;
    }

    function getCountByPropType(mixed $type): int{
        $filtered = array_filter($this->records, function ($record) use ($type) {
            return $record instanceof $type;
        });
        return count($filtered);
    }
}
