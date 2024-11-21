<?php

namespace SmartDownloader\Services\DownloadService;

use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces\DownloadConnectorInterface;
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


    public function handleProgress(DownloadDataClass $downloadData ): void {

       
    }

    
    
    public function reportStatus(bool  $multipart, string  $status, string  $message): void {
        if($status == "complete"){
        
        }
    }
    
    /**
     * Handles the progress of a download by adding the provided download data to the downloads array.
     *
     * @param DownloadDataClass $download_data The data related to the current download.
     *
     * @return void
     */
    public function start(string $url, int $chunk_size, TransactionDataClass $transaction): void {
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
    }



    /**
     * Stops the current download process.
     *
     * @param string $message Optional. A message to be logged or displayed when the download is stopped.
     *
     * @return void
     */
    public function stop($message = ""): void {
        $this->connectorPlugin->stopDownload($message);
    }


    /**
     * Resumes a download from the given URL. TO be implemented in the future.
     *
     * @param string $url The URL of the file to resume downloading.
     * @param int $chunkSize The size of the chunks to download.
     * @param int $byteOffset The offset in bytes to resume the download from.
     *
     * @return void
     */
    public function resume(string $url, int $chunkSize, int $byteOffset){

    }
}
