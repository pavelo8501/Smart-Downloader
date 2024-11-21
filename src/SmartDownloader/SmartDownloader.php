<?php

namespace SmartDownloader;

use Closure;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Models\ServiceConfiguration;
use SmartDownloader\Services\ListenerService\ListenerService;
use SmartDownloader\Services\LoggingService\LoggingService;
use SmartDownloader\Services\UpdateService\UpdateService;
use SmartDownloader\Services\UpdateService\UpdateServicePlugins\PostgresConnector;
use SmartDownloader\Services\ListenerService\Models\DataContainer;

class SmartDownloader {

    public static LoggingService $logger;
    private static SDConfiguration $config;

    private static ListenerService $listenerServices;
    private ?DataContainer $dataContainer = null;
    protected ?UpdateService $updateService = null;
    private static ListenerService $listenerService ;

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    public function __construct()
    {
        self::$config = new SDConfiguration();
        $this->initUpdater();
        $this->initDataContainer();
    }

    public function initUpdater($connector = null): UpdateService {
        if($this->updateService === null) {
            $this->updateService = new UpdateService(new PostgresConnector());
        }
        if($connector){
            $this->updateService->updaterPlugin = $connector;
        }
        return $this->updateService;
    }

    /**
     * @throws OperationsException
     * @throws DataProcessingException
     */
    protected function initDataContainer(): void{
         $this->dataContainer = new  DataContainer(
             [$this->updateService, "onGetTransactions"],
             null);
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