<?php

namespace SmartDownloader;

use SmartDownloader\Models\SDConfiguration;

class SmartDownloader
{
    private SDConfiguration $config;
    
    public function __construct(callable $callback = null){
        if ($callback) {
            $callback($this->config);
        }
    }

}

