<?php

namespace SmartDownloader\Services\ListenerService;

use PhpParser\Node\Expr\CallLike;
use PHPUnit\Framework\Constraint\Callback;
use SmartDownloader\Models\Download;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\SmartDownloader;

class ListenerService{

    private SmartDownloader $parentSD;

    private DataContainer $transactionContainer;
    private FileDownloadService $fileDownloader;


    private function initUpdater(Callback $convert): void {
        if(is_null($this->transactionContainer)){
            $this->transactionContainer = new DataContainer(TransactionDataClass::class, $convert);
        }
    }
    
    public function convert(DownloadRequest $download): TransactionDataClass {

        $newTransaction = new TransactionDataClass();
        $newTransaction->url  =   $download->url;
        $newTransaction->path  =  $download->path;
        return $newTransaction;
    }

    public function __construct(SmartDownloader $parent, Callback $convert){
       // $this->parentSD = $parent;
        $this->fileDownloader = new FileDownloadService();

        if(!is_null($convert)){
            $this->initUpdater($convert);
        }
    }

    public function download(DownloadRequest $downloadRequest): void {
       $count = $this->transactionContainer->getCountByPropType(TransactionDataClass::$status::IN_PROGRESS);
        if($count <= 5){
          $connectionRequest = $this->fileDownloader->initializeDownload($downloadRequest);
          $this->transactionContainer->registerNewConnection($connectionRequest);
        }
    }
}