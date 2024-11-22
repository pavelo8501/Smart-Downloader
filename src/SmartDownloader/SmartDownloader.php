<?php

namespace SmartDownloader;

use Closure;
use Fiber;
use PDO;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Models\ServiceConfiguration;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\CurlServiceConnector;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\RequestDataClass;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Enums\ListenerTasks;
use SmartDownloader\Services\ListenerService\ListenerService;
use SmartDownloader\Services\LoggingService\LoggingService;
use SmartDownloader\Services\UpdateService\UpdateService;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\SqlCommonConnector;

class SmartDownloader {

    public static LoggingService $logger;
    public static SDConfiguration $config;

    private static ListenerService $listenerServices;
    private ?DataContainer $dataContainer = null;
    protected ?UpdateService $updateService = null;
    private static ListenerService $listenerService ;

    protected SqlCommonConnector $connector;

    protected  array $fibers = [];

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    public function __construct(?SqlCommonConnector $db_connector){
        $this::$logger = new LoggingService();
        if($db_connector == null){
            throw new OperationsException(
                "DataConnector not provided unable to process data",
                OperationsExceptionCode::SETUP_FAILURE
            );
        }
        $this->connector = $db_connector;
        self::$config = new SDConfiguration();
        $this->initUpdater();
        $this->initDataContainer();
        $this->initListener();
    }

    public  function getFiberByProcessId(int $processId): Fiber{
       return  $this->fibers[$processId];
    }

    public function initUpdater(SqlCommonConnector $connector = null): UpdateService {
        if($this->updateService === null) {
            if($connector){
                $this->updateService = new UpdateService($connector);
            }else{
                $this->updateService = new UpdateService($this->connector);
            }
        }
        return $this->updateService;
    }


    private function fiberInitialization(TransactionDataClass $transaction):int{

        $fiber = new Fiber(function ($transaction):  void {
            echo "Executing inside Fiber...\n";
            $downloader  = new FileDownloadService(new CurlServiceConnector());
            $downloader->start($transaction);
        });
        $process_index = count($this->fibers);
        $this->fibers[$process_index] = $fiber;
        return $process_index;
    }

    protected function initListener(): void {
        self::$listenerService = new ListenerService($this, $this->dataContainer);
        self::$listenerService->subscribeTasksInitaiated(function (ListenerTasks $task, TransactionDataClass $transaction ) {
            if($task == ListenerTasks::DOWNLOAD_STARTED){
                LoggingService::info("Listener: New download task initiated");
                $process_index = $this->fiberInitialization($transaction);
                $reported_value =   $this->fibers[$process_index]->start($transaction);
                var_dump($reported_value);
            }
        });
        LoggingService::info("Download process started");
    }

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    protected function initDataContainer(): void{
         $this->dataContainer = new  DataContainer([$this->updateService, "onGetTransactions"], null);
         $this->dataContainer->subscribeToTransactionUpdates([$this->updateService, "onUpdateTransaction"], "updateService");
         $this->dataContainer->subscribeToTransactionUpdates([$this->updateService, "onUpdateTransaction"], "updateService");
    }

    public function getRequest(ApiRequest | array $request):void{
        if(is_array($request)){
            $transformedRequest =  new ApiRequest();
            $transformedRequest->action = $request['action'];
            $transformedRequest->file_url = $request['file_url'];
            $request = $transformedRequest;
        }
        $config_array = null;
        if($request->action == "start"){
            $config_array =  self::$config->getConfigurationArray();
        }
        self::$listenerService->processRequest($request, $config_array);
        LoggingService::info("Request {$request->action} processed");
        if($request->action == "start"){
            sleep(10);
            $newRequest = ["action"=>"stop", "file_url" => "https://storage.googleapis.com/public_test_access_ae/output_60sec.mp4"];
            $this->getRequest($newRequest);
        }
    }

    public function configure(Closure $context) {
        $config = new ServiceConfiguration();
        $context->call($config); // Binds $this inside the closure to $config
    }
}