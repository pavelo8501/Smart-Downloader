<?php
namespace SmartDownloader\Services\UpdateService;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\SqlCommonConnector;

class UpdateService{


    public SqlCommonConnector $updaterPlugin;
    public function __construct(UpdateConnectorInterface $plugin){
        $this->updaterPlugin = $plugin;
    }
    public function saveTransaction(TransactionDataClass $transaction): void{
        $this->saveTransaction($transaction);
    }

    public function getTransaction(int $id): TransactionDataClass | null{
        

        return null;
    }

    public function updateTransaction(TransactionDataClass $transaction): void{

    }

    public function getTransactions(): array | null {
        $transactions =  $this->updaterPlugin->getTransactions(true);
        return $transactions;
    }

    public  function  onGetTransactions(): array | null {
        return $this->getTransactions();
    }

}