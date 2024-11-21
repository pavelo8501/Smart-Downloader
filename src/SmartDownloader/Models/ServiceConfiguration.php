<?php

namespace SmartDownloader\Models;

use SmartDownloader\Services\LoggingService\Enums\LogLevel;
use SmartDownloader\Services\LoggingService\LoggingService;

class ServiceConfiguration {

    public static SDConfiguration $config;

    

    public function __construct(){
        
    }



    /**
     * Subscribes to logging events with a specified minimum log level and a callback function.
     * @param LogLevel $minLogLevel The minimum log level to trigger the callback.
     * @param callable(LogLevel, string, string): void $callbackFunction The function to be called when a log event occurs.
     * @return void
     */
    public function subscribeLogging(LogLevel $minLogLevel, callable $callbackFunction){

    }


    public function contextFunction(int $ten): void{
        $a = $ten;
    }


    public int  $testA =0;



}