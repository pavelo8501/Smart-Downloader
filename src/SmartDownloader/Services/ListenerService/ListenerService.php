<?php

namespace SmartDownloader\Services\ListenerService;

use Closure;
use Exception;
use PhpParser\Node\Expr\Throw_;
use SmartDownloader\Enumerators\ChunkSize;
use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\Services\LoggingService\LoggingService;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\CurlServiceConnector;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Enums\ListenerTasks;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateService;
use function Symfony\Component\String\s;

class ListenerService{

    private SmartDownloader $parent;

    private DataContainer $transactionContainer;

    public array  $onTaskCallbacks = [];

    public static function reportResult(TransactionDataClass $transaction, ListenerTasks $forTask):string {
        $response = [];
        switch ($forTask){
            case ListenerTasks::ON_START:
                $response = ["id" => $transaction->id, "status" => "OK"];
                break;
            case ListenerTasks::ON_PAUSE:
                $response = ["id" => $transaction->id, "status" => "STOP"];
                break;
            case ListenerTasks::ON_RESUME:
                $response = ["id" => $transaction->id, "status" => "RESUME"];
                break;
            case ListenerTasks::ON_CANCEL:
                $response = ["id" => $transaction->id, "status" => "CANCELLED"];
        }
        return  json_encode($response);
    }

    public static function reportRejected(TransactionDataClass $transaction):string{
        $response = [
            "file_url" => $transaction->file_url,
            "status" => "REJECTED",
            "message" => "Maximum number of simultaneous downloads   exceeded",
        ];
        return  json_encode($response);
    }


    public function __construct(SmartDownloader $parent, DataContainer $transactionContainer){
        $this->parent = $parent;
        $this->transactionContainer = $transactionContainer;
    }

    protected function notifyOnTask(ListenerTasks  $task,TransactionDataClass $transaction):void{
        if(array_key_exists($task->value, $this->onTaskCallbacks)){
            call_user_func($this->onTaskCallbacks[$task->value],$task ,$transaction);
        }else{
            LoggingService::warn("Task [$task->value] not initialized");
        }
    }

    public function initializeDownload(ApiRequest $request):void{
        $count = $this->transactionContainer->getCountByPropType("status", TransactionStatus::IN_PROGRESS);
        $config =  $this->parent->configuration->properties;
        $downloadRequest = new DownloadRequest();
        $downloadRequest->file_url = $request->file_url;
        $downloadRequest->file_path =  $config->temp_dir;
        $newTransaction = $this->transactionContainer->registerNew($downloadRequest);
        $newTransaction->chunk_size =ChunkSize::MB_5->value;
        $newTransaction->retry_await_time = $config->retry_await_time;
        if($count >  $config->max_downloads && $config->rate_exceed_action == RateExceedAction::QUE){
            $newTransaction->status = TransactionStatus::SUSPENDED;
        }
        if($count >  $config->max_downloads && $config->rate_exceed_action == RateExceedAction::CANCEL) {
            ListenerService::reportRejected($newTransaction);
        }

        $this->notifyOnTask(ListenerTasks::ON_START, $newTransaction);
        $this->parent->issueCommand("downloader", "start", $newTransaction);

        $this->parent->issueCommand("downloader", "stop", $newTransaction);
    }

    public ?Closure $onDownloadResume = \null;
    protected function resumeDownload(ApiRequest $request): void
    {
        $transactions =  $this->transactionContainer->getByPropertyValue("file_url", $request->file_url);
        if(count($transactions) > 0){
            $this->notifyOnTask(ListenerTasks::ON_RESUME, $transactions[0]);
        }
    }

    protected function stopDownload(ApiRequest $request):void {
        $transactions =  $this->transactionContainer->getByPropertyValue("file_url", $request->file_url);
        if(count($transactions)>0){
            $this->notifyOnTask(ListenerTasks::ON_PAUSE, $transactions[0]);
        }
    }
    protected function cancelDownload(ApiRequest $request):void{

    }


    public function subscribeTasksInitiated(ListenerTasks $task, callable $callback):void{
        try {
            $this->onTaskCallbacks[$task->value] = $callback;
        }catch (Exception $exception){
            LoggingService::error($exception->getMessage());
        }
    }

    /**
     * Undocumented function
     *
     * @param ApiRequest $request
     * @return void
     */
    public function processRequest(ApiRequest $request, ?array $config = null):void{
        LoggingService::info("New request received: {$request->action}");
        switch ($request->action){
            case "start":
                if($config == \null){
                    LoggingService::warn("Config not received on start download");
                }
                $this->initializeDownload($request);
                break;
            case "stop":
                $this->stopDownload($request);
                LoggingService::info("Stopping download : {$request->file_url}");
                break;
            case "resume":
                $this->resumeDownload($request);
                break;
            case "cancel":
                $this->cancelDownload($request);
                break;
            default:
                //Report unknown command;
                return;
        }
    }
}