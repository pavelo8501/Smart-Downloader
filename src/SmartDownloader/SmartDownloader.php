<?php

namespace SmartDownloader;

use Closure;
use SmartDownloader\Models\ApiRequest;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Models\ServiceConfiguration;
use SmartDownloader\Services\ListenerService\ListenerService;
use SmartDownloader\Services\LoggingService\LoggingService;

class SmartDownloader {

    private static LoggingService $logger;
    private static SDConfiguration $config;
    private static $listenerServices = [];

    private static ListenerService $listenerService ;

    private function registerService($listenerService, $requestorUrl):void {
        self::$listenerServices [$requestorUrl] = $listenerService;
    }
    
    public function processRequest(ApiRequest $request):void{
        if(!$this->listenerService){
            $this->listenerService = new ListenerService($this, self::$config);
        }
        $this->listenerService->processRequest($request);
    }

    public function configure(callable $callback) {
         
        $callback = function () use ($callback) {
            $callback(new ServiceConfiguration());
        };
        $callback->call($this->config);
    }


    // public function configure(callable $callback): ServiceConfiguration {
    //     $callback = function () use ($callback) {
    //         $callback($this);
    //     };
    //     $callback->call($this->config);
    //     return new ServiceConfiguration();
    // }
   
    // public function configure(callable $callback): use (ServiceConfiguration {
    //     $callback($this->config);
    //     return new ServiceConfiguration();
    // }
}