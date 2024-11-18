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

    public function convert(DownloadRequest $download): TransactionDataClass {
        $newTransaction = new TransactionDataClass();
        $newTransaction->url  =   $download->url;
        $newTransaction->path  =  $download->path;
        return $newTransaction;
    }

    public function __construct(SmartDownloader $parent, Callback $convert){
        // $this->parentSD = $parent;
        $this->transactionContainer = new DataContainer(TransactionDataClass::class, $convert);
        $this->fileDownloader = new FileDownloadService($this->transactionContainer);
    }

    public function  initializeConnection(DownloadRequest $request){
        $this->fileDownloader->initializeDownload($request);
    }

    public function download(DownloadRequest $downloadRequest): void {
       $count = $this->transactionContainer->getCountByPropType(TransactionDataClass::$status::IN_PROGRESS);
        if($count <= 5){
          $connectionRequest = $this->fileDownloader->initializeDownload($downloadRequest);
        }
    }
}