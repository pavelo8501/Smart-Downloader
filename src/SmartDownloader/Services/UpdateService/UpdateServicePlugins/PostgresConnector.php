<?php

namespace SmartDownloader\Services\UpdateService\UpdateServicePlugins;

use Medoo\Medoo;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\SmartDownloader;

class PostgresConnector extends SqlCommonConnector implements UpdateConnectorInterface {

    public  Medoo $db_postgres;

    public function __construct(
    ){
        $this->initDB();
        parent::__construct();
    }

    private function initDB(): void
    {
        $this->db_postgres = new Medoo([
            'type' => 'pgsql',
            'host' => 'localhost',
            'database' => 'downloader_db',
            'username' => 'postgresuser',
            'password' => 'somepassword'
        ]);
    }

    private function reinitIfNull(): void{
        try {
            if($this->db_postgres->error) {
                $this->db_postgres = new Medoo([
                    'type' => 'pgsql',
                    'host' => 'localhost',
                    'database' => 'downloader_db',
                    'username' => 'postgresuser',
                    'password' => 'somepassword'
                ]);
            }
        }catch (\Exception $e){
            SmartDownloader::$logger->error($e);
        }
    }

    public function getTransactions():array | null{
        $this->reinitIfNull();
        try {
            $this->db_postgres->select("transactions",$result[] = [],"*");
            return $result;
        }catch (\Exception $e){
            $error = $this->db_postgres->error;
            $properties = TransactionDataClass::class->keyProperties;
            $created = $this->db_postgres->create("transactions", $properties);
            if($created){
                return [];
            }
            return null;
        }
    }
}
