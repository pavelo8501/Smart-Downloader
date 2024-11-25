<?php

namespace SmartDownloader\Services\UpdateService\UpdateServicePlugins;

use Exception;
use PDO;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;

abstract class PDOCommonConnector implements UpdateConnectorInterface {

    protected PDO $db_connector;

    public function __construct(PDO $db) {
            $this->db_connector = $db;
    }
    private function initConnector(): void{
//        if($this->db_connector->isConnected() == false){
//            $this->db_connector->connect();
//        }
    }

    public  function strForSql(string $str): string {
        $cleanSql = preg_replace('/[\x00-\x1F\x7F]/', '', $str);
        $cleanSql = preg_replace('/\s+/', ' ', $cleanSql);
        return trim($cleanSql);
    }

    public function recreateTable(string $table_name, array $columns, mixed $arrayValues):bool{
        try {
            $table_name = strtolower($table_name);
            $dropSql = "DROP TABLE IF EXISTS {$table_name};";
            $stmt_drop = $this->db_connector->prepare($dropSql);

             $sqlQuery = "";
             $this->strForSql("CREATE TABLE {$table_name}
                        id SERIAL PRIMARY KEY,");
            foreach ($columns as $key => $value) {
                $str = "";
                switch ($value[$key]->Type){
                    case "string":
                        $str =  "{$value[$key]} VARCHAR NOT NULL";
                     break;
                     case "int":
                         $str = "{$value[$key]} INT DEFAULT 0";
                         break;
                     case "array":
                         $str =  "{$value[$key]} ARRAY({$value[$key]})";
                 break;
                }
              $sqlQuery .= $this->strForSql($str.",");
            }
            $sqlQuery = rtrim($sqlQuery,",");
            $stmt_create = $this->db_connector->prepare($sqlQuery);
            $this->db_connector->beginTransaction();
            $stmt_drop->execute();
            $stmt_create->execute();
            $this->db_connector->commit();
            return true;
        }catch (\Exception $exception){
            $this->db_connector->rollback();
            throw new DataProcessingException($exception->getMessage(), DataProcessingExceptionCode::DATASOURCE_CREATE_FAIL);
        }
    }

    protected function select(array $params): array | null {
        try {
            $this->initConnector();
            $statuses  = "";
            foreach ($params as $parameter) {
                foreach ($parameter as $key => $value) {
                    $statuses .=  "{$value}, ";
                }
            }
            $statuses = trim($statuses, ", ");
            $sql =  $this->strForSql("SELECT * FROM  transactions WHERE status IN  ({$statuses})");
            $stmt = $this->db_connector->prepare($sql);
           // $stmt->bindValue(':status1', TransactionStatus::IN_PROGRESS->value, PDO::PARAM_INT);
           // $stmt->bindValue(':status2', TransactionStatus::SUSPENDED->value, PDO::PARAM_INT);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $transactions = [];
            foreach ($records as $record) {
                $transaction =  new TransactionDataClass($record);
                if(!is_null($record["transaction_data"])){
                    $transaction->setTransactionData(json_decode($record["transaction_data"], true));

                }
                $transactions[]= $transaction;
            }
            return  $transactions;
        } catch (\Exception $exception) {
            throw  new DataProcessingException($exception->getMessage(), DataProcessingExceptionCode::DATASOURCE_SELECT_FAIL );
        }
    }

    protected function pickTransaction(int $transaction_id) : TransactionDataClass | null{
        $this->initConnector();
        $sql =  $this->strForSql("SELECT id, file_path, file_url FROM  transactions WHERE status = :status");
        $stmt = $this->db_connector->prepare($sql);
        $stmt->execute([':id' => $transaction_id]);
        $record = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($record) == 0){
            return null;
        }
        $transaction = new  TransactionDataClass();
        $transaction->initFromAssociative($record);
        return $transaction;
    }

    protected function patchTransaction(TransactionDataClass $transaction):int{
        $this->initConnector();
        try {
            $transaction_data = json_encode($transaction->transactionData);
            $sql = $this->strForSql("UPDATE  transactions
            SET  bytes_saved = :bytes_saved , status = :status, transaction_data = :transaction_data, can_resume = :can_resume WHERE id = :id");
            $stmt = $this->db_connector->prepare($sql);
            $stmt->execute([$transaction->bytes_saved, $transaction->status->value, $transaction_data,
                $transaction->can_resume, $transaction->id]);

            return $transaction->id;
        }catch (\Exception $exception){
            throw  new DataProcessingException($exception->getMessage(), DataProcessingExceptionCode::DATASOURCE_SELECT_FAIL );
        }
    }

    public function pickData(int $transaction_id): TransactionDataClass | null{
        return null;
    }

    protected function postTransaction(TransactionDataClass $transaction ): int{
        try {
            $this->initConnector();
            $sql = $this->strForSql("INSERT INTO transactions  
            (file_path, file_url, chunk_size, bytes_saved, status)
            VALUES (:file_path, :file_url, :chunk_size, :bytes_saved, :status);");

            $stmt = $this->db_connector->prepare($sql);
            $stmt->execute([$transaction->file_path, $transaction->file_url, $transaction->chunk_size, $transaction->bytes_saved, $transaction->status->value]);
            $id = $this->db_connector->lastInsertId();
            $transaction->id = $id;
            return $id;
        }catch (\Exception $exception){
            throw  new DataProcessingException($exception->getMessage(), DataProcessingExceptionCode::DATASOURCE_INSERT_FAIL);
        }
    }

    protected function delete($table_name, $params ):bool{
        $this->initConnector();
        try {

            $where = "";
            foreach ($params as $key => $value) {
                $where .= "{$key} = {$key} AND";
            }
            $where = rtrim($where, " AND");
            $sql = $this->strForSql("DELETE FROM {$table_name}
            WHERE {$where}");
            $stmt = $this->db_connector->prepare($sql);
            $stmt->execute();
            return true;
        }catch (Exception $exception){
            throw  new DataProcessingException($exception->getMessage(),
                DataProcessingExceptionCode::DATASOURCE_DELETE_FAIL);
        }
    }

    public function saveData() {
    }

    public function updateData() {

    }

    public function selectData():array|null {
        return [];
    }

    public function createTable(string $table_name, array $columns){
        
    }
}
