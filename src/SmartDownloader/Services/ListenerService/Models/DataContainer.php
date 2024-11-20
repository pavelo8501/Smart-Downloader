<?php


namespace SmartDownloader\Services\ListenerService\Models;

use Closure;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Interfaces\TransactionDataContainer;


class DataContainer implements TransactionDataContainer{

    /**
     * The records in the container.
     *
     * @var array[TransactionDataClass]
     */
    private array $records = [];

    private ?Closure $onRecordUpdated = null;

    private ?Closure $onDataRequested = null;
  
    public function __construct(
        ?callable $onDataRequested = null
    ){
        if($onDataRequested != null){
            if(!is_callable($onDataRequested)){
                throw new DataProcessingException(
                    "onDataRequested supplied to DataContainer is not callable",
                    DataProcessingExceptionCode::INVALID_DATA_SUPPLIED
                );
            }
            $this->onDataRequested = Closure::fromCallable($onDataRequested);
            $this->requestData();
        }
    }



    private function requestData(): void {
        if ($this->onDataRequested != null) {
            $transactions =  call_user_func($this->onDataRequested);
            if (count($transactions)>0) {
                if (!$transactions[0] instanceof TransactionDataClass) {
                    throw new DataProcessingException("Data requested must be an array of TransactionDataClass objects", DataProcessingExceptionCode::INVALID_DATA_SUPPLIED);
                }
                foreach ($transactions as $transaction) {
                    $transaction->setOnUpdatedCallback([$this, 'onTransactionUpdated']);
                    $this->records[] = $transaction;
                }
            }
        }
    }


    /**
     * @return array[TransactionDataClass] The records in the container.
     */
    private function getRecordsByProperty(string $property, mixed $value):array{

        $filtered = array_filter($this->records, function ($record) use ($property, $value) {
            if ($record->getKeyProperties()[$property] == $value) {
                return $record;
            }
        });

        return $filtered;
    }


    /**
     * Callback to handle transaction updates.
     *
     * @param TransactionDataClass $transaction The transaction that was updated.
     */
    function onTransactionUpdated(TransactionDataClass $transaction):void{
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
    function getCountByPropType(string $property, mixed $value): int{

        $filtered =  $this->getRecordsByProperty($property, $value);
        return count($filtered);
    }

    /**
     * Gets a transaction by a property value.
     *
     * @param string $property The property to search by.
     * @param mixed $value The value to search for.
     * @return  array[TransactionDataClass] The transaction found.
     */
    function getByPropertyValue(string $property, mixed $value):array{
        $filtered =  $this->getRecordsByProperty($property, $value);
        return $filtered;
    }

}
