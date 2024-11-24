<?php

namespace SmartDownloader\Services\UpdateService\UpdateServicePlugins;

use PDO;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;


class PostgresConnector extends PDOCommonConnector{

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
    public function getTransactions(array $transactions):array | null{
        return $this->select($transactions);
    }

    public function getTransaction(int $transaction_id) :?TransactionDataClass{
       return  $this->pickTransaction($transaction_id);
    }

    public function deleteTransactions(array $transactions): bool{
        $result = array_map(function($transaction) use($transactions){
            return  ["id"=>$transaction->id];
         },$transactions);
        $this->delete("transactions", $result);
        return true;
    }

}
