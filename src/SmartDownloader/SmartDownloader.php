<?php

namespace SmartDownloader;

use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Models\ServiceConfiguration;
use SmartDownloader\Services\ListenerService\ListenerService;

class SmartDownloader {


    private static SDConfiguration $config;
    private static $listenerServices = [];

    private static ListenerService $listenerService ;

    private function registerService($listenerService, $requestorUrl):void {
        self::$listenerServices [$requestorUrl] = $listenerService;
    }

    public function initializeDownloadDepreciated(string $requestorUrl, mixed $requestorId = null){
        $filtered = array_filter(self::$listenerServices, function($service) use ($requestorUrl){
            return $service->requestorUrl == $requestorUrl;
        });
        if(count($filtered)>0){
            return $filtered[0];
        }
        $newService = new ListenerService($this, self::$config);
        $request = new DownloadRequest();
        //$newService->initializeConnection($request);
        $this->registerService($newService, $requestorUrl);
    }

    public function startDownloadDepriciated(string $requestorUrl){
        $service = self::$listenerServices[$requestorUrl];
        if($service != null){
            $service->download();
        }
    }

    public function processRequest(ApiRequest $request):void{
        if(!$this->listenerService){
            $this->listenerService = new ListenerService($this, self::$config);
        }
        $this->listenerService->processRequest($request);
    }
   
    public function configure(callable $callback): ServiceConfiguration {
        $callback($this->config);
        return new ServiceConfiguration();
    }
}