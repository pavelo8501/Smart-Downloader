<?php


namespace SmartDownloader\Services\ListenerService\Models;

use Exception;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Util\Http\Downloader;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\Download;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Interfaces\TransactionDataContainer;


class DataContainer implements TransactionDataContainer{

    private array $records = [];

    public function __construct(){
       
    }

    function registerNew(DownloadRequest $download):TransactionDataClass{
        $newTransaction = new TransactionDataClass();
        $download->copy($newTransaction);
        $this->records[] = $newTransaction;
        return $newTransaction;
    }

    function getCountByPropType(mixed $type): int{
        $filtered = array_filter($this->records, function ($record) use ($type) {
            return $record instanceof $type;
        });
        return count($filtered);
    }

    function getByValue(string $property, mixed $value):TransactionDataClass{
        $filtered = array_filter($this->records, function ($record) use ($property,$value) {
            if($record->properties[$property]->value == $value){
                return $record->properties[$property]->value;
            }
        });

        if(count($filtered)>0){
            return $filtered[0];
        }
        throw new DataProcessingException("No object found for property {$property} and  value: {$value}", DataProcessingExceptionCode::NO_PROPERTY_BY_VALUE);
    }
}
