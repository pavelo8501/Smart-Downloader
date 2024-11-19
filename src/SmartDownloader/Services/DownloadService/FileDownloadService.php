<?php

namespace SmartDownloader\Services\DownloadService;

use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Services\DownloadService\DownloadConnectorInterface;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Models\DataContainer;

class FileDownloadService {

    private DownloadConnectorInterface $connectorPlugin;

    private DownloadRequest $request;

    private TransactionDataClass $currentTransaction;


    public function __construct(DownloadConnectorInterface $connectorPlugin) {
        $this->connectorPlugin = $connectorPlugin;
    }

    //content-range
    //content-length: 1024
    // Accept: */*
    //Range: bytes=0-1023

    public function handleProgress(
        int $bytesStarted,
        int $bytesTransferred,
        int $bytesMax
    ): void {
        $downloadData = new DownloadDataClass();
        $downloadData->bytesStarted = $bytesStarted;
        $downloadData->bytesTransferred = $bytesTransferred;
        $downloadData->bytesMax = $bytesMax;
    }

    public function start(string $url, int $chunkSize, TransactionDataClass $transaction): DownloadRequest {

        $this->currentTransaction = $transaction;
        
        $this->connectorPlugin->downloadFile(
            $url,
            $chunkSize,
            [$this, 'handleProgress']
        );
        return $this->request;
    }

    public function stop(){

    }

    public function resume(string $url, int $chunkSize, int $byteOffset){
        
    }
}
