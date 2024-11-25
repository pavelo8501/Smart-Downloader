<?php
namespace SmartDownloader\Services\UpdateService;

use Exception;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\LoggingService\LoggingService;
use SmartDownloader\Services\UpdateService\Enums\DataOperationType;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PDOCommonConnector;

class UpdateService{

    public PDOCommonConnector $updaterPlugin;
    public function __construct(UpdateConnectorInterface $plugin){
        $this->updaterPlugin = $plugin;
    }
    public function saveTransaction(TransactionDataClass $transaction): int{
        try {
           return $this->updaterPlugin->saveTransaction($transaction);
        }catch (DataProcessingException $exception) {
            if ($transaction->id == 0) {
                LoggingService::error("Initial data save fail. Cancel download", $exception);
                return 0;
            } else {
                LoggingService::warn("SaveTransaction reported exception {$exception->getMessage()}");
                return 0;
            }
        }catch (Exception $exception){
            LoggingService::error("General unprocessed exception {$exception->getMessage()}", $exception);
            throw $exception;
        }
    }

    public function getTransaction(int $id): TransactionDataClass | null{
        try {
            return $this->updaterPlugin->getTransaction(1);
        }catch (DataProcessingException $exception){
            LoggingService::error("Data processing exception {$exception->getMessage()}");
            return null;
        }catch (Exception $exception){
            LoggingService::error("General unprocessed exception {$exception->getMessage()}", $exception);
            throw $exception;
        }
    }


    public function getTransactions(array $transactions): array | null {
        $transactions =  $this->updaterPlugin->getTransactions($transactions);
        return $transactions;
    }

    public function deleteTransaction(array $transactions): bool | null {
        return $this->deleteTransaction($transactions);
    }
    public function onDataRequested(DataOperationType $requestType, mixed $data): mixed {
        try {
            switch ($requestType) {
                case DataOperationType::Save:
                   return $this->saveTransaction($data);
                case DataOperationType::Delete:
                    return $this->deleteTransaction($data);
                case DataOperationType::Get:
                   return $this->getTransactions($data);
            }
            return true;
        }catch (DataProcessingException $exception){
            LoggingService::error("Data processing exception {$exception->getMessage()}");
            return false;
        }
    }
}