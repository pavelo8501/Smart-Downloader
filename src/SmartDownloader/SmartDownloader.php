<?php

namespace SmartDownloader;

use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Services\ListenerService\ListenerService;

class SmartDownloader
{
    private SDConfiguration $config;
    private ListenerService $listenerService;


    public static function registerService(ListenerService $service){
        self::$listenerService = $service;
    }


    public function __construct(callable $callback = null){
        if ($callback) {
            $callback($this->config);
        }
    }

    function makeConnection(DownloadRequest $request){
        $this->listenerService->download($request);
    }

}

