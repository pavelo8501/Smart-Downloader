<?php

namespace SmartDownloader\Services\ListenerService;

use Closure;
use PhpParser\Node\Expr\Throw_;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\CurlServiceConnector;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Enums\ListenerTasks;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateService;

class ListenerService{

    public string  $file_download_path;

    private SmartDownloader $parent;
    private SDConfiguration $config;

    private ApiRequest $currentRequest;

    private DataContainer $transactionContainer;
    private ?FileDownloadService $fileDownloader = null;
    
    public ?Closure $onTaskInitiated = null;


    public function __construct(
        SmartDownloader $parent,
        DataContainer $transactionContainer
    ){
        $this->parent = $parent;
        $this->transactionContainer = $transactionContainer;
        if ($this->fileDownloader === null) {
            $this->fileDownloader = new FileDownloadService(new CurlServiceConnector());
        }

        //$this->transactionContainer = new DataContainer($this->updatator->getTransactions());
    }


    private function notifyTaskInitiated(ListenerTasks  $task ,TransactionDataClass $transaction){
        if($this->onTaskInitiated){
            call_user_func($this->onTaskInitiated, $task ,$transaction);
        }
    }

    private function initializeDownload(ApiRequest $request){
        if($this->fileDownloader === null){
            $this->fileDownloader = new FileDownloadService(new CurlServiceConnector());
        }

        $count = $this->transactionContainer->getCountByPropType("status", TransactionStatus::IN_PROGRESS);
        if ($count <= $this->config->max_downloads) {
            $downloadRequest = new DownloadRequest();
            $downloadRequest->file_url = $request->file_url;
            $downloadRequest->file_path = $this->config->download_dir + "//filename.ext";
            $newTransaction = $this->transactionContainer->registerNew($downloadRequest);
            $this->fileDownloader->start($request->file_url, $this->config->chunk_size, $newTransaction);
            $this->notifyTaskInitiated(ListenerTasks::DOWNLOAD_STARTED, $newTransaction);
        }
    }

    private function pauseDownload(ApiRequest $request){
        $transactions = $this->transactionContainer->getByPropertyValue("url", $request->file_url);
        if(!$this->fileDownloader){
            throw new OperationsException("file downloader not initialized", OperationsExceptionCode::SOURCE_UNDEFINED);
        }
        //$this->fileDownloader->stop();
       // $this->notifyTaskInitiated(ListenerTasks::DOWNLOAD_STARTED, $transaction);
    }

    private function notifyResumeDownload(ApiRequest $request){
       
    }
    public ?Closure $onDownloadResume = \null;
    protected function resumeDownload(ApiRequest $request){
        $found_transaction =  $this->transactionContainer->getByPropertyValue("file_url", $request->file_url);
        if ($found_transaction == \null) {
            //TO DO REQUES FROM DB
        }
        $this->notifyResumeDownload($found_transaction);
        if($this->onDownloadResume == \null){
            throw new OperationsException("onDownloadResume Callback not initialized in Listener", OperationsExceptionCode::KEY_CALLBACK_UNINITIALIZED);
        }

        // if($this->updatator !== null){
        //     $this->updatator = new UpdateService(new PostgresConnector());
        //     $transaction = $this->updatator->getTransaction(0);
        //     if ($this->fileDownloader !== null) {
        //          $this->fileDownloader->resume($transaction->file_url, $this->config->chunk_size, $transaction->bytes_saved);
        //     }
        // }
    }

    private function cancelDownload(ApiRequest $request) {
        if ($this->fileDownloader !== null) {
            $this->fileDownloader->stop();
        }
    }

    public function subscribeTasksInitaiated(callable $callback){
        $this->onTaskInitiated = Closure::fromCallable($callback);
    }

    /**
     * Undocumented function
     *
     * @param ApiRequest $request
     * @return void
     */
    public function processRequest(ApiRequest $request, ?array $config = \null):void{
        $this->currentRequest = $request;
        switch ($request->action){
            case "start":
                if($config == \null){
                    $this->parent::$logger::warn("Config not received on start download");
                }
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