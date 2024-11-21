<?php
namespace SmartDownloader\Services\UpdateService;

use Medoo\Medoo;
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
    public function saveTransactions(array $transactions): void{
        $transactions = array_map(fn($transaction) => $transaction->toAssocArray(), $transactions);
        $this->updaterPlugin->createTable("transactions", $transactions);
    }

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    public function getTransaction(int $id): TransactionDataClass{
        
        
        $data = $this->updaterPlugin->pickData($id);
        if(!$data){
            throw new OperationsException("Transaction not found", OperationsExceptionCode::TRANSACTION_NOT_FOUND);
        }
        $transaction =  new TransactionDataClass();
        $transaction->loadFromArray($data);
        return $transaction;
    }

    public function getTransactions(): array | null {

        $select =  $this->updaterPlugin->getTransactions();
         $transactions = [];
             foreach($select as $key=>$value){
                 $tr = new TransactionDataClass();
                 $tr[$key]->value = $value;
                 $transactions[] = $tr;
             }
         return $transactions;
    }

    public  function  onGetTransactions(): array | null {
        return $this->getTransactions();

    }

}