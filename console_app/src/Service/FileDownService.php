<?php


namespace Console\Service;

use PDO;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\SmartDownloader;

class FileDownService extends SmartDownloader{

    protected PDO $pdo;
   protected UpdateConnectorInterface $connector;

    public function __construct()
    {
        $this->pdo = new PDO("pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}",
            $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connector = new PostgresConnector($this->pdo);
        parent::__construct($connector);
    }


}