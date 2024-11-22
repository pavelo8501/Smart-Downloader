<?php

namespace SmartDownloader;

use Closure;
use PDO;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Models\ServiceConfiguration;
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

    protected function initListener(): void {
        self::$listenerService = new ListenerService($this, $this->dataContainer);
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



    public function getRequest(ApiRequest $request):void{
        // if(!$this->listenerService){
        //     $this->listenerService = new ListenerService($this, $container);
        // }
        if($request->action == "start"){
            $configArray =  self::$config->getConfigurationArray();
        }
        self::$listenerService->processRequest($request, $configArray);
    }

    public function configure(Closure $context) {
        $config = new ServiceConfiguration();
        $context->call($config); // Binds $this inside the closure to $config
    }
}