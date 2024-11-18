<?php


namespace SmartDownloader\Services\ListenerService\Models;

use Exception;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Util\Http\Downloader;
use SmartDownloader\Models\Download;
use SmartDownloader\Models\DownloadRequest;
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
           $transaction =  call_user_func_array($this->converter);

            if($transaction != null){
                return $transaction;
            }
            return null;
        }


        return null;
    }

    function newRecord(DownloadRequest $download): TransactionDataClass | null{

        $transaction  = $this->conversion($download);
        $this->records[] = $transaction;
        return $transaction;
    }

    function getCountByPropType(mixed $type): int{
        $filtered = array_filter($this->records, function ($record) use ($type) {
            return $record instanceof $type;
        });
        return count($filtered);
    }
}
