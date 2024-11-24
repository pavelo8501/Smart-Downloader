<?php

namespace SmartDownloader\Services\DownloadService;

use Exception;
use Fiber;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\CurlServiceConnector;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\Plugin\CurlAsync;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\ListenerService\ListenerService;
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
    private array $download_chunks = [];

    private SmartDownloader $smartDownloader;


    public function __construct(SmartDownloader $smart_downloader) {
        $this->smartDownloader = $smart_downloader;
    }

   public function sendCommand($command,  TransactionDataClass $transaction){

   }


    public function onRequestReceived(string $command, ?TransactionDataClass $transaction = null) {


       switch ($command) {
           case "stop":{
               LoggingService::info("STOP RECEIVED");
               break;
           }
           case "start":{
               $data_reader = new DownloadDataClass();
               $transaction->copyData($data_reader);
               $transaction->setDownloadDataClass($data_reader);
               $connector =  new CurlAsync($transaction->file_url, $transaction->file_path,  $data_reader);
               break;
           }
       }
       if($connector == null){ return null; }
        $connector->initializeDownload(function (CurlAsync $connector) use ($transaction, $data_reader) {
            try{
                $ch =  $connector->readHeader($data_reader);
                $status = $connector->readFile($ch, $transaction->file_path, $data_reader);
                if(!$status){
                    sleep($data_reader->retry_await_time);
                    $connector->retryLoop($ch, $data_reader);
                }
                $transaction->status = TransactionStatus::COMPLETE;
                LoggingService::event("{Transaction id {$transaction->id} complete");
            }catch (OperationsException $ex){
                if($ex->getCode() !== OperationsExceptionCode::CONNECTOR_READ_FAILURE){
                   $transaction->status = TransactionStatus::FAILED;
                    LoggingService::error("{Transaction id {$transaction->id} failed to resume  }");
                   if($transaction->bytes_saved != $data_reader->bytes_read_to){
                       LoggingService::error("{Transaction id {$transaction->id} file {$transaction->file_url}  is corrupt }");
                       $transaction->can_resume = false;
                       $transaction->status = TransactionStatus::CORRUPTED;
                   }
                }
            }catch (Exception $exception){
                LoggingService::error($exception->getMessage());
            }
        });
    }


    public function setConnectorPlugin(DownloadConnectorInterface $connectorPlugin){
        $this->connectorPlugin = $connectorPlugin;
    }


    private function splitToReportableParts(int $reporting_interval_count) {
        $chunk_count = (int)(($this->currentTransaction->file_size - $this->currentTransaction->bytes_saved) / $this->currentTransaction->chunk_size);
        if($reporting_interval_count < $chunk_count ) {
            $download_chunk_size = ($chunk_count / $reporting_interval_count * $this->currentTransaction->chunk_size);
        }else{
            $download_chunk_size = $this->currentTransaction->chunk_size;
        }
        $start_byte = $this->currentTransaction->bytes_saved;
        while ($start_byte  < $this->currentTransaction->file_size) {
            $index = 0;
            $start_byte += $download_chunk_size;
            $this->download_chunks[$index] = ["byte_offset"=>$start_byte, "reported" =>false];
        }
    }

    public function handleProgress(DownloadDataClass $downloadData): void {
        LoggingService::event("{$downloadData->bytes_start} / {$downloadData->bytes_read_to}");
        $this->currentTransaction->file_size = $downloadData->bytes_read_to;
       $to_report = array_filter($this->download_chunks, function ($key, $value) use ($downloadData) {
            return ($this->download_chunks[$key]["byte_offset"]  < $downloadData->bytes_start && ($this->download_chunks[$key]["reported"] == false));
        });
       if(count($to_report) > 0){
           LoggingService::info("Downloaded {$downloadData->bytes_start} / {$downloadData->bytes_read_to} of $downloadData->bytes_max");
           foreach ($to_report as $value) {
               $value["reported"] = true;
           }
       }
    }

    public function reportStatus(bool  $can_resume, TransactionStatus  $status, string  $message): void {
        if($status == TransactionStatus::IN_PROGRESS){
            $this->currentTransaction->status = $status;
            $this->currentTransaction->can_resume = $can_resume;
            $this->currentTransaction->notifyUpdated($this->currentTransaction);
            $this->splitToReportableParts(100);
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

    public function initializeDownload(mixed $data_object)
    {

    }
}
