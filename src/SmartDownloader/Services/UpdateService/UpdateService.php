<?php
namespace SmartDownloader\Services\UpdateService;

use Exception;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\LoggingService\LoggingService;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\SqlCommonConnector;

class UpdateService{


    public SqlCommonConnector $updaterPlugin;
    public function __construct(UpdateConnectorInterface $plugin){
        $this->updaterPlugin = $plugin;
    }
    public function saveTransaction(TransactionDataClass $transaction): void{
        try {
            $this->updaterPlugin->saveTransaction($transaction);
        }catch (DataProcessingException $exception){
            if($transaction->id == 0){
                LoggingService::error( "Initial data save fail. Cancel download",$exception);
            }else{
                LoggingService::warn( "SaveTransaction reported exception {$exception->getMessage()}");
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


    public function getTransactions(): array | null {
       // $this->updaterPlugin->recreateTable("transactions",[]);
        $transactions =  $this->updaterPlugin->getTransactions();
        return $transactions;
    }

    public  function  onGetTransactions(): array | null {
        return $this->getTransactions();
    }

    public function onUpdateTransaction(TransactionDataClass $transaction): bool{
        $this->saveTransaction($transaction);
        return true;
    }


}