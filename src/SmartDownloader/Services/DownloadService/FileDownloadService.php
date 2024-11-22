<?php

namespace SmartDownloader\Services\DownloadService;

use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\LoggingService\LoggingService;
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
        LoggingService::event("{$downloadData->bytes_start} / {$downloadData->bytes_read_to}");
    }

    
    
    public function reportStatus(bool  $can_resume, TransactionStatus  $status, string  $message): void {
        if($status == TransactionStatus::IN_PROGRESS){
            $this->currentTransaction->status = $status;
            $this->currentTransaction->can_resume = $can_resume;
            $this->currentTransaction->notifyUpdated($this->currentTransaction);
        }
    }
    
    /**
     * Handles the progress of a download by adding the provided download data to the downloads array.
     *
     * @param DownloadDataClass $download_data The data related to the current download.
     *
     * @return void
     */
    public function start(TransactionDataClass $transaction): void {
        $this->currentTransaction = $transaction;
        $download_data = new DownloadDataClass();
        $transaction->copyData($download_data);
        $transaction->setDownloadDataClass($download_data);
        $this->connectorPlugin->downloadFile(
            $transaction->file_url,
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
