<?php

namespace SmartDownloader;

use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Services\ListenerService\ListenerService;

class SmartDownloader
{
    private SDConfiguration $config;


    private ListenerService $listenerService;

    
    public function __construct(callable $callback = null){
       // $this->listenerService
        if ($callback) {
            $callback($this->config);
        }
    }

}

