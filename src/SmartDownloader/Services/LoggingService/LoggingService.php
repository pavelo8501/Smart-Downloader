<?php

namespace SmartDownloader\Services\LoggingService;

use Closure;
use Exception;
use SmartDownloader\Services\LoggingService\Enums\LogLevel;
use Throwable;

/**
 * Class LogSubscription
 * Represents a subscription to log messages.
 * @property Closure $callback   The callback function to be executed when a log message is received.
 * @property LogLevel $min_level The minimum log level required for the callback to be executed.
 * @property string $message     The log message.
 */
class LogSubscription{
    public Closure $callback;
    public LogLevel $min_level;

    public function __construct(LogLevel $min_level, callable  $callback){
        $this->min_level = $min_level;
        $this->callback = $callback;
    }
}


class LoggingService{

    /**
     * Get the current GMT date and time in RFC 2822 format.
     * @return string The current GMT date and time.
     */
    public static function getCurrentGmt():string{
        return gmdate('r', time());
    }


    /**
     * An array that holds the log subscriptions.
     * This is a static property, meaning it is shared across all instances of the class.
     * @var array[LogSubscription]  $logSubscriptions
     */
    private static $logSubscriptions = [];


    protected static function log(LogLevel $level, string|Throwable $message, Throwable $exception = null){
        try{
            foreach (self::$logSubscriptions as  $logSubscription){
                if($logSubscription->min_level->value <= $level->value){
                    if($message instanceof  Throwable ){
                    $trace = $message->getTrace();
                        $formattedThrowable = "Exception: " . $message->getMessage() . PHP_EOL;
                        foreach ($trace as $index => $frame) {
                            $file = $frame['file'] ?? '[internal function]';
                            $line = $frame['line'] ?? 'unknown';
                            $function = $frame['function'] ?? 'unknown';
                            $formattedThrowable .= "#$index $file:$line $function()" . PHP_EOL;
                        }
                        call_user_func($logSubscription->callback, $formattedThrowable, self::getCurrentGmt());
                    }else{
                        call_user_func($logSubscription->callback, $message, self::getCurrentGmt());
                    }
                }
            }
        }catch(Exception $e){
            print_r($e);
        }
    }


    /**
     * Subscribes a callback function to log events with a specified minimal log level.
     * @param LogLevel $minimalLevel The minimal log level for which the callback should be triggered.
     * @param callable(string, string)$callbackFunction The callback function to be executed when a log event occurs.
     */
    public static function subscribe(LogLevel $minimalLevel, callable $callbackFunction){
        if(is_callable($callbackFunction)){
           $newSubscription = new LogSubscription($minimalLevel, $callbackFunction);
           self::$logSubscriptions[] = $newSubscription;
        }
    }

    
    public static function info(string $message) {
        self::log(LogLevel::MESSAGE, $message);
    }

    public static function event(string $message){
        self::log(LogLevel::EVENT, $message);
    }

    public static function warn(string $message){
        self::log(LogLevel::WARNING, $message);
    }

    public static function error(string $message, ?Throwable $e = null):void{
        self::log(LogLevel::HANDLED_EXCEPTION, $message, $e);
    }

}