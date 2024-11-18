<?php

namespace SmartDownloader\Services\ListenerService;

use SmartDownloader\Models\Download;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

private ?DataContainer $transactionContainer = null;

class ListenerService{

    public function convert(Download $download): TransactionDataClass{
        $newTransaction = new TransactionDataClass();
        $newTransaction->url  =   $download->url;
        $newTransaction->path  =  $download->path;
        return $newTransaction;
    }

    private function initUpdater($convert): void {
        $this->updateService = new UpdateService(new PostgresConnector());
        $this->transactionContainer = new DataContainer(TransactionDataClass::class, $convert);
    }

    public function __construct(SmartDownloader $parent, $convert){
        $this->parentSD = $parent;
        $this->initUpdater($convert);
    }
}