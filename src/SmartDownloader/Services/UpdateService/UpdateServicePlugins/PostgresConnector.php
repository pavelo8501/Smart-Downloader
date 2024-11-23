<?php

namespace SmartDownloader\Services\UpdateService\UpdateServicePlugins;

use PDO;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\SmartDownloader;

class PostgresConnector extends SqlCommonConnector implements UpdateConnectorInterface{

    public function __construct(PDO $db){
        parent::__construct($db);
    }

    function saveTransaction(TransactionDataClass $transaction):int{
        if($transaction->id == 0){
            return $this->postTransaction($transaction);
        }else{
             $this->patchTransaction($transaction);
              return $transaction->id;
        }
    }
    public function getTransactions():array | null{
        return $this->fetchTransactions();
    }

    public function getTransaction(int $transaction_id) :?TransactionDataClass{
       return  $this->pickTransaction($transaction_id);
    }
}
