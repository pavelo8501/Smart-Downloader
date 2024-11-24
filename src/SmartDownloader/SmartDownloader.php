<?php

namespace SmartDownloader;

use Closure;
use PDO;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\ConfigProperties;
use SmartDownloader\Models\ServiceConfiguration;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\CurlServiceConnector;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Enums\ListenerTasks;
use SmartDownloader\Services\ListenerService\ListenerService;
use SmartDownloader\Services\LoggingService\LoggingService;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\Services\UpdateService\UpdateService;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PDOCommonConnector;

class SmartDownloader {

    public  LoggingService $logger;

    public ServiceConfiguration $configuration;

    private ?DataContainer $dataContainer = null;

    protected ?FileDownloadService $fileDownloadService = null;

    protected ?UpdateService $updateService = null;
    private  ListenerService $listenerService ;

    protected UpdateConnectorInterface $connector;


    protected PDO $pdo;

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    public function __construct(?UpdateConnectorInterface $connector = null){
        $this->logger = new LoggingService();
        $this->connector = $connector;
        if($connector == null){
            $this->pdo = new PDO("pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}",
                $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connector = new PostgresConnector($this->pdo);
        }
       // $this-> config = new ConfigProperties();
        $this->configuration = new ServiceConfiguration();
        $this->initUpdater();
        $this->initDataContainer();
        $this->initListener();
    }

    public function issueCommand($source, $type, mixed $data_object){
        switch($source){
            case "downloader":
                switch($type){
                    case "start":
                    {
                        ($this->fileDownloadService == null) ?? $this->fileDownloadService = new FileDownloadService($this);
                        $this->fileDownloadService->onRequestReceived("start", $data_object);
                        break;
                    }
                    case "stop" : {
                           if ($this->fileDownloadService != null){
                               $this->fileDownloadService->onRequestReceived("stop",null);
                           }
                    }
                    break;
                }
        }
    }
    public function configure(callable $call): void{
        $configuration = $this->configuration;
        $call($configuration);
        $this->configuration = $configuration;
    }

    public function initUpdater(?PDOCommonConnector $connector = null): UpdateService {
        if($this->updateService == null) {
            if($connector != null){
                $this->updateService = new UpdateService($connector);
            }else{
                $this->updateService = new UpdateService($this->connector);
            }
        }
        return $this->updateService;
    }

    protected function initListener(): void {
        $this->listenerService = new ListenerService($this, $this->dataContainer);
        $this->listenerService->subscribeTasksInitiated(ListenerTasks::ON_START, function (ListenerTasks $task, TransactionDataClass $transaction) {
            http_response_code(200);
            echo ListenerService::reportResult($transaction, $task);
            sleep(10);
        });
        $this->listenerService->subscribeTasksInitiated(ListenerTasks::ON_PAUSE, function (ListenerTasks $task, TransactionDataClass $transaction) {
            http_response_code(200);
            echo ListenerService::reportResult($transaction, $task);
        });
        $this->listenerService->subscribeTasksInitiated(ListenerTasks::ON_RESUME, function (ListenerTasks $task, TransactionDataClass $transaction) {
            http_response_code(200);
            echo ListenerService::reportResult($transaction, $task);
        });
        $this->listenerService->subscribeTasksInitiated(ListenerTasks::ON_CANCEL, function (ListenerTasks $task, TransactionDataClass $transaction) {
            http_response_code(200);
            echo ListenerService::reportResult($transaction, $task);
        });
    }

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    protected function initDataContainer(): void{
         $this->dataContainer = new  DataContainer([$this->updateService, "onDataRequested"]);
         $this->dataContainer->subscribeToTransactionUpdates([$this->updateService, "saveTransaction"], "updateService");
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
            $config_array =  $this->configuration->properties->getConfigurationArray();
        }
        $this->listenerService->processRequest($request, $config_array);
        LoggingService::info("Request {$request->action} processed");


    }


}