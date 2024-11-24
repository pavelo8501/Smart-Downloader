<?php


namespace SmartDownloader\Services\ListenerService\Models;

use Closure;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Interfaces\TransactionDataContainer;
use SmartDownloader\Services\LoggingService\LoggingService;

use SmartDownloader\Services\UpdateService\Enums\DataOperationType;
use function PHPUnit\Framework\callback;

class DataContainer implements TransactionDataContainer{

    /**
     * The records in the container.
     *
     * @var array[TransactionDataClass]
     */
    private array $records = [];

    public ?Closure $RequestTransactionsHistory = null;
    private array $onRecordUpdatedCallbacks = [];
    private ?Closure $dataRequestCallback = null;

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    public function __construct(
        ?callable $dataRequestCallback = null
    ){
        if($dataRequestCallback != null){
            if(!is_callable($dataRequestCallback)){
                throw new DataProcessingException(
                    "onDataRequested supplied to DataContainer is not callable",
                    DataProcessingExceptionCode::INVALID_DATA_SUPPLIED
                );
            }
            $this->dataRequestCallback = $dataRequestCallback(...);
            $this->onInitialized();
        }
    }

    private function onInitialized():void{
        $whereStatus = [];
        $whereStatus[] =   ["status" => TransactionStatus::IN_PROGRESS->value];
        $whereStatus[] = ["status" => TransactionStatus::SUSPENDED->value];
        $transactions =  call_user_func($this->dataRequestCallback, DataOperationType::Get, $whereStatus);
        if (count($transactions) > 0 ) {
            foreach ($transactions as $transaction) {
                $transaction->setOnUpdatedCallback([$this, 'onTransactionUpdated']);
                $this->records[] = $transaction;
            }
        }
    }


    /**
     * Sets the function to retrieve the transactions history.
     * @param callable(null): array[TransactionDataClass] $getTransactions A callable function that retrieves the transactions history.
     */
    public function subscribeToTransactionUpdates(callable $transactionUpdatedCallback, string $subscriber): void{
        if(is_callable($transactionUpdatedCallback)){
            $this->onRecordUpdatedCallbacks[$subscriber] = $transactionUpdatedCallback;
        }else{
            throw new DataProcessingException("Subscription failed. Callback provided is invalid", DataProcessingExceptionCode::INVALID_DATA_SUPPLIED);
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
        foreach ($this->onRecordUpdatedCallbacks as $callback){
            call_user_func($callback, $transaction);
        }
    }


    /**
     * Registers a new transaction.
     *
     * @param DownloadRequest $download The download request to register.
     * @return TransactionDataClass The registered transaction.
     */
    function registerNew(DownloadRequest $download):TransactionDataClass{
        
        $newTransaction = new TransactionDataClass();
        $download->copyData($newTransaction);
        $newTransaction->setOnUpdatedCallback([$this, 'onTransactionUpdated']);
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
        if(count($filtered) == 0){
            LoggingService::event("No data found for {$property} with value in the container {$value}");
            LoggingService::info("Looking in database");

        }
        return $filtered;
    }

}
