<?php

namespace SmartDownloader;

use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Services\ListenerService\ListenerService;

class SmartDownloader
{
    private SDConfiguration $config;
    private ListenerService $listenerService;

    public function __construct(callable $callback){
        $this->config = new SDConfiguration();
        if ($callback) {
            $callback($this->config);
        }
    }

    public static function registerService(ListenerService $service) {
        self::$listenerService = $service;
    }

    function makeConnection(string $url, string $path){
        $request = new DownloadRequest($url = $url, $url = $path);
        $this->listenerService->download($request);
    }

}

