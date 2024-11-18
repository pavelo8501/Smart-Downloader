<?php

namespace SmartDownloader\Services\ListenerService;

use SmartDownloader\Models\Download;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

class ListenerService{

    public function convert(Download $download): TransactionDataClass{
        $newTransaction = new TransactionDataClass();
        $newTransaction->url  =   $download->url;
        $newTransaction->path  =  $download->path;
        return $newTransaction;
    }
}