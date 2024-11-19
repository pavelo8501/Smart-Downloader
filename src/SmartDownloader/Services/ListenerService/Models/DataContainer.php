<?php


namespace SmartDownloader\Services\ListenerService\Models;

use Closure;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Interfaces\TransactionDataContainer;


class DataContainer implements TransactionDataContainer{

    private array $records = [];

    private ?Closure $onRecordUpdated = null;
  
    public function __construct(){
       
    }


    /**
     * Callback to handle transaction updates.
     *
     * @param TransactionDataClass $transaction The transaction that was updated.
     */
    protected function onTransactionUpdated(TransactionDataClass $transaction):void{
        if($this->onRecordUpdated != null ){
            call_user_func($this->onRecordUpdated, $transaction);
        }
    }

    /**
     * Subscribe to record  updates  of the container.
     *
     * @param callable $callback The callback to subscribe.
     */
    function subscribeUpdates(callable $callback):void{
        $this->onRecordUpdated = Closure::fromCallable($callback);
    }

    /**
     * Registers a new transaction.
     *
     * @param DownloadRequest $download The download request to register.
     * @return TransactionDataClass The registered transaction.
     */
    function registerNew(DownloadRequest $download):TransactionDataClass{
        $newTransaction = new TransactionDataClass([$this, 'onTransactionUpdated']);
        $download->copy($newTransaction);
        $this->records[] = $newTransaction;
        return $newTransaction;
    }

    /**
     * Removes a transaction.
     *
     * @param TransactionDataClass $transaction The transaction to remove.
     * @return bool True if the transaction was removed, false otherwise.
     */
    function remove(TransactionDataClass $transaction):bool{
        $index = array_search($transaction, $this->records);
        if($index !== false){
            unset($this->records[$index]);
            return true;
        }
        return false;
    }

    /**
     * Gets the count of transactions by type.
     *
     * @param mixed $type The type to count.
     * @return int The count of transactions by type.
     */
    function getCountByPropType(mixed $type): int{
        $filtered = array_filter($this->records, function ($record) use ($type) {
            return $record instanceof $type;
        });
        return count($filtered);
    }

    /**
     * Gets the count of transactions by property.
     *
     * @param string $property The property of record object.
     * @param mixed $value The value to of the property.
     * @return int The count of transactions by property.
     */
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

    function getByPropertyValue(string $property, mixed $value):TransactionDataClass{

        
        $filtered = array_filter($this->records, function ($record) use ($property,$value) {
            if($record->properties[$property] == $value){
                return $record->properties[$property];
            }
        });

        if(count($filtered)>0){
            return $filtered[0];
        }
        throw new DataProcessingException("No object found for property {$property} and  value: {$value}", DataProcessingExceptionCode::NO_PROPERTY_BY_VALUE);
    }

}
