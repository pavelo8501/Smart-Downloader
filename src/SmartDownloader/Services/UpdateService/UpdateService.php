<?php
namespace SmartDownloader\Services\UpdateService;

use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;

class UpdateService{

    public PostgresConnector $updatorPlugin;

    public function __construct(UpdateConnectorInterface $plugin){
        if (!is_null(new $plugin)){
            $this->updatorPlugin = $plugin;
        }
    }

    public function getTransaction(int $id): TransactionDataClass{

        $data = $this->updatorPlugin->pickData($id);

        if(!$data){
            throw new OperationsException("Transaction not found", OperationsExceptionCode::TRANSACTION_NOT_FOUND);
        }

        $transaction =  new TransactionDataClass();
        $transaction->loadFromArray($data);

        return $transaction;
    }

    public function getTransactions(): array {

        $data = $this->updatorPlugin->selectData();

        // if(!$data){
        //     throw new OperationsException("Transaction not found", OperationsExceptionCode::TRANSACTION_NOT_FOUND);
        // }

        $transactions = [];

        if($data){
            foreach ($data as $record) {
                $transaction =  new TransactionDataClass();
                $transaction->loadFromArray($record);
                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }
}


