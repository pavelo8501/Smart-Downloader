<?php

namespace SmartDownloader\Services\UpdateService\UpdateServicePlugins;

use PDO;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;

abstract class SqlCommonConnector implements UpdateConnectorInterface {

    protected PDO  $db_connector;

    public function __construct($db) {
        try {
            $this->db_connector = $db;
        }catch (\Exception $exception){
            throw new OperationsException($exception->getMessage(), OperationsExceptionCode::COMPONENT_UNINITIALIZED );
        }
    }
    private function initConnector(): void{
//        if($this->db_connector->isConnected() == false){
//            $this->db_connector->connect();
//        }
    }

    public  function strForSql(string $str): string {

        $cleanSql = preg_replace('/[\x00-\x1F\x7F]/', '', $str);
        $cleanSql = preg_replace('/\s+/', ' ', $cleanSql);
        $cleanSql = trim($cleanSql);
        return $cleanSql;
    }

    public function recreateTable(string $table_name, array $columns):bool{

        $table_name = strtolower($table_name);
        $dropSql = "DROP TABLE IF EXISTS {$table_name};";
        $createSql = $this->strForSql( "CREATE TABLE {$table_name} (
                id SERIAL PRIMARY KEY,
                file_url VARCHAR NOT NULL,
                file_path VARCHAR,
                chunk_size INT,
                bytes_saved INT,
                status SMALLINT DEFAULT 0
            );"
        );
        $this->db_connector->beginTransaction();;
        $this->db_connector->exec($dropSql);
        $this->db_connector->exec($createSql);
        $this->db_connector->commit();

        return true;
    }

    protected function fetchTransactions(): array | null {
        try {
            $this->initConnector();
            $sql =  $this->strForSql("SELECT id, file_path, file_url FROM  transactions WHERE status = :status");
            $stmt = $this->db_connector->prepare($sql);
            $stmt->execute([':status' => 1]);
            $records = $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return  $records;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return null;
        }
    }

    protected function patchTransaction(TransactionDataClass $transaction):bool{
        $this->initConnector();

    }

    protected function postTransaction(TransactionDataClass $transaction ): int{
        try {
            $this->initConnector();
            $sql = $this->strForSql("INSERT INTO TRANSACTION  
            (file_path, file_url, chunk_size, bytes_saved, status)
            VALUES (:file_path, :file_url, :chunk_size, :bytes_saved, :status);");

            $stmt = $this->db_connector->prepare($sql);
            $stmt->execute([$transaction->file_path, $transaction->file_url, $transaction->chunk_size, $transaction->bytes_saved, $transaction->status]);
            $id = $this->db_connector->lastInsertId();
            $transaction->id = $id;
            return $id;
        }catch (\Exception $exception){
            throw  new OperationsException($exception->getMessage(), OperationsExceptionCode::DATASOURCE_INSERT_FAIL );
        }
    }

    public function saveData() {
    }

    public function updateData() {
    }

    public function selectData():array|null {
        return [];
    }

    public function pickData():mixed{
        return null;
    }

    public function createTable(string $table_name, array $columns){
        
    }
}
