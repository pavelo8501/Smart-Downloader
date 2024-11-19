<?php

namespace SmartDownloader\Services\ListenerService;

use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\CurlServiceConnector;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\UpdateService\UpdateConnectorPlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateService;

class ListenerService{

    public string  $file_download_path;

    private SmartDownloader $parent;
    private SDConfiguration $config;

    private ApiRequest $currentRequest;

    private DataContainer $transactionContainer;
    private FileDownloadService $fileDownloader;
    private UpdateService $updatator;

    

    // public function convert(DownloadRequest $download): TransactionDataClass {
    //     $newTransaction = new TransactionDataClass();
    //     $newTransaction->url  =   $download->url;
    //     $newTransaction->path  =  $download->path;
    //     return $newTransaction;
    // }

    public function __construct(
        SmartDownloader $parent,
        SDConfiguration $config
    ){
        $this->parent = $parent;
        $this->config = $config;
        $this->transactionContainer = new DataContainer();

        if (!$this->fileDownloader) {
            $this->fileDownloader = new FileDownloadService(new CurlServiceConnector());
        }
        if (!$this->updatator) {
            $this->updatator = new UpdateService(new PostgresConnector());
        }
    }


    private function initializeDownload(ApiRequest $request){
        if(!$this->fileDownloader){
            $this->fileDownloader = new FileDownloadService(new CurlServiceConnector());
        }
        if (!$this->updatator) {
            $this->updatator = new UpdateService(new PostgresConnector());
        }

        $count = $this->transactionContainer->getCountByPropType(TransactionDataClass::$status::IN_PROGRESS);
        if ($count <= $this->config->max_downloads) {
            $downloadRequest = new DownloadRequest(
                
            );
            $downloadRequest->file_url = $request->file_url;
            $downloadRequest->file_path = $this->config->download_dir + "//filename.ext";
            echo var_dump($downloadRequest);
            $newTransaction = $this->transactionContainer->registerNew($downloadRequest);
            echo var_dump($newTransaction);
            $a = 10;
            //$this->fileDownloader->startDownload($request->file_url, $this->config->chunk_size, $newTransaction);
        }
    }

    private function pauseDownload(ApiRequest $request){
        $transaction = $this->transactionContainer->getByValue(TransactionDataClass::$url, $request->file_url);
        
        if(!$this->fileDownloader){
            throw new OperationsException("file downloader not initialized", OperationsExceptionCode::SOURCE_UNDEFINED);
        }
        //$this->fileDownloader->stop();
    }

    private function resumeDownload(ApiRequest $request){
        
        $transaction = $this->updatator->getTransaction(0);
        $this->fileDownloader->resume($transaction->url, $this->config->chunk_size, $transaction->bytes_saved);

    }

    private function cancelDownload(ApiRequest $request) {
        $this->fileDownloader->stop();
    }


    /**
     * Undocumented function
     *
     * @param ApiRequest $request
     * @return void
     */
    public function processRequest(ApiRequest $request):void{
        $this->currentRequest = $request;
        switch ($request->action){
            case "start":
                $this->initializeDownload($request);
                break;
            case "pause":
                $this->pauseDownload($request);
                break;
            case "resume":
                $this->resumeDownload($request);
                break;
            case "cancel":
                $this->cancelDownload($request);
                break;
        }
    }
}