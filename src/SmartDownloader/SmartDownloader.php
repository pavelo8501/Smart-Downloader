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


    private function fiberInitialization(TransactionDataClass $transaction):Fiber{
        $fiber = new Fiber(function ($transaction): void {
            echo "Executing inside Fiber...\n";
            Fiber::suspend('Paused');
            $downloader  = new FileDownloadService(new CurlServiceConnector());
            $downloader->start($transaction);

            echo "Resumed Fiber execution!\n";
        });
        return $fiber;
    }



    protected function initListener(): void {
        self::$listenerService = new ListenerService($this, $this->dataContainer);
        self::$listenerService->subscribeTasksInitaiated(function (  ListenerTasks $task, TransactionDataClass $transaction ) {
            if($task == ListenerTasks::DOWNLOAD_STARTED){
                $downloader  = new FileDownloadService(new CurlServiceConnector());
                LoggingService::info("Listener: New download task initiated");
                $fiber = $this->fiberInitialization($transaction);
                $fiber->start($transaction);
            }
        });
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
    }

    public function configure(Closure $context) {
        $config = new ServiceConfiguration();
        $context->call($config); // Binds $this inside the closure to $config
    }
}