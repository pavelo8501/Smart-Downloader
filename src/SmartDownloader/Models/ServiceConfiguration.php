<?php

namespace SmartDownloader\Models;

use SmartDownloader\Services\LoggingService\Enums\LogLevel;

class ServiceConfiguration {


    /**
     * Subscribes to logging events with a specified minimum log level and a callback function.
     * @param LogLevel $minLogLevel The minimum log level to trigger the callback.
     * @param callable(LogLevel, string, string): void $callbackFunction The function to be called when a log event occurs.
     * @return void
     */
    public function subscribeLogging(LogLevel $minLogLevel, callable $callbackFunction){

    }

}