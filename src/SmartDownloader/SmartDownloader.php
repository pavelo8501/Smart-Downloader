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
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\Enums\ListenerTasks;
use SmartDownloader\Services\ListenerService\ListenerService;
use SmartDownloader\Services\LoggingService\LoggingService;
use SmartDownloader\Services\UpdateService\Interfaces\UpdateConnectorInterface;
use SmartDownloader\Services\UpdateService\UpdateService;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\SqlCommonConnector;

class SmartDownloader {

    public  LoggingService $logger;
    public  SDConfiguration $config;

    private static ListenerService $listenerServices;
    private ?DataContainer $dataContainer = null;
    protected ?UpdateService $updateService = null;
    private static ListenerService $listenerService ;

    protected UpdateConnectorInterface $connector;

    protected  array $fibers = [];

    protected PDO $pdo;


    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    public function __construct(?UpdateConnectorInterface $connector = null){
        $this->logger = new LoggingService();
        if($connector == null){
            $this->pdo = new PDO("pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}",
                $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connector = new PostgresConnector($this->pdo);
        }
        $this->config = new SDConfiguration();
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
            $data = Fiber::suspend("Waiting for data...");
            $downloader->start($transaction);
        });
        $process_index = count($this->fibers);
        $this->fibers[$process_index] = $fiber;
        return $process_index;
    }

    protected function initListener(): void {
        self::$listenerService = new ListenerService($this, $this->dataContainer);
        self::$listenerService->subscribeTasksInitaiated(ListenerTasks::DOWNLOAD_STARTED, function (ListenerTasks $task, TransactionDataClass $transaction ) {
            if($task == ListenerTasks::DOWNLOAD_STARTED){
                LoggingService::info("Listener: New download task initiated");
                $process_index = $this->fiberInitialization($transaction);
                 $reported_value =  $this->fibers[$process_index]->start($transaction);
                  while ($this->fibers[$process_index]->isSuspended()) {
                      $response["status"] = "ok";
                      $json_output = json_encode($response);
                      http_response_code(response_code: 200);
                      echo $json_output;
                      $this->fibers[$process_index]->resume();
                  }
                var_dump($reported_value);
                LoggingService::info("Listener: download processing");
            }
        });

        self::$listenerService->subscribeTasksInitaiated(ListenerTasks::DOWNLOAD_PAUSED, function (ApiRequest $request) {
            $this->fibers[0]->resume($request);
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
            $config_array =  $this->config->getConfigurationArray();
        }
        self::$listenerService->processRequest($request, $config_array);
        LoggingService::info("Request {$request->action} processed");
        $newRequest = ["action"=>"stop", "file_url" => "https://storage.googleapis.com/public_test_access_ae/output_60sec.mp4"];
        $this->getRequest($newRequest);
    }

    public function configure(Closure $context) {
        $config = new ServiceConfiguration();
        $context->call($config); // Binds $this inside the closure to $config
    }
}