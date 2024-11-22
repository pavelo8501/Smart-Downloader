<?php

namespace SmartDownloader\Handlers;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\LoggingService\LoggingService;

abstract class DataClassBase {

    protected \Closure $onUpdatedCallback;

    protected array  $keyProperties;

    public function __construct(array $keyProperties = null){
        if($keyProperties) {
            $this->keyProperties = $keyProperties;
        }
    }

    public function notifyUpdated(DataClassBase $sourceObject):void{

        call_user_func($this->onUpdatedCallback, $sourceObject);
    }

    public function setOnUpdatedCallback(callable $onUpdateCallback):void{
        if(is_callable($onUpdateCallback)){
            $this->onUpdatedCallback = $onUpdateCallback(...);
        }else{
            throw new OperationsException("On update callback is not callable",OperationsExceptionCode::KEY_CALLBACK_UNINITIALIZED);
        }
    }


    public function copyData(DataClassBase $toSource, $strict = false): DataClassBase{

        foreach ($this->keyProperties as $key => $value ){
            if(array_key_exists ($key, $toSource->keyProperties)){
                $toSource->{$key} = $this->{$key};
            }else{
                if($strict){
                    throw new DataProcessingException("On Data Class copy property name {$key} was missing", DataProcessingExceptionCode::PROPERTY_MISSING);
                }
            }
        }
        return $toSource;
    }

    public static function toAssocArray(DataClassBase  $object):array {

       $result[] = [];
       foreach ($object->keyProperties as  $key => $value ) {
           $result[$key] =  $object->{$key};
       }
        return $result;
    }
    public function initFromAssociative(array $data){
        foreach ($data as $key => $value){
            if(array_key_exists($key, $this->keyProperties)){
                $this->{$key} = $value;
            }else{
                if(!property_exists($this, $key)){
                    LoggingService::warn("Failed to initialize property {$key} for TransactionDataClass");
                }
            }
        }
    }


}
