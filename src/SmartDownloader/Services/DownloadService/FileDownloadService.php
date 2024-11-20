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

    private array $downloads = [];


    public function __construct(DownloadConnectorInterface $connectorPlugin) {
        $this->connectorPlugin = $connectorPlugin;
    }

    //content-range
    //content-length: 1024
    // Accept: */*
    //Range: bytes=0-1023

    public function reportStatus(
        bool  $multipart,
        string  $status,
        string  $message
    ): void {

        if($status == "complete"){
            $val = 10;
        }

       echo "{$multipart} | {$status} | {$message} ";
    }

    public function handleProgress(
        DownloadDataClass $download_data,
    ): void {
        $this->downloads[] = $download_data;
    }

    public function start(string $url, int $chunk_size, TransactionDataClass $transaction): DownloadRequest {
        $this->currentTransaction = $transaction;
        $download_data = new DownloadDataClass();
        $transaction->copy($download_data);
        $download_data->chunk_size = $chunk_size;
        $val1 =  $download_data->chunk_size;


        $this->connectorPlugin->downloadFile(
            $url,
            $download_data,
            [$this, 'reportStatus'],
            [$this, 'handleProgress']
        );
        return $this->request;
    }

    public function stop(){

    }

    public function resume(string $url, int $chunkSize, int $byteOffset){

    }
}
