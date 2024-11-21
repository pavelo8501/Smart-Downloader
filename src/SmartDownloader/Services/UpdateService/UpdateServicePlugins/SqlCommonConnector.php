<?php

namespace SmartDownloader\Services\UpdateService\UpdateServicePlugins;

use Medoo\Medoo;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;

abstract class SqlCommonConnector implements UpdateConnectorInterface {

    public Medoo $db_postgres;

    private function initDB(): void{


    }


    public function __construct() {
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
