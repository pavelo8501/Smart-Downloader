<?php

namespace SmartDownloader\Handlers;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Handlers\DataHandlerTrait;


abstract class DataClassBase{

    use DataHandlerTrait;

    public array $keyProperties = [];

    public function reflectProtectedProperties(): void {
        if (count($this->keyProperties) == 0) {
            $child_class = new \ReflectionClass($this);
            $allProperties = $child_class->getProperties();
            $protectedProperties = array_filter(
                $allProperties,
                fn($property) => $property->isProtected()
            );
            foreach($protectedProperties as $property){
                $this->keyProperties[$property->getName()] = $property->getValue($this);
            }
        }
    }


    public function __get($name) {
       $this->reflectProtectedProperties();
       if(key_exists($name, $this->keyProperties)){
           return $this->keyProperties[$name];
       }
    }

    public function __set($name, $value) {
        $this->reflectProtectedProperties();
        if (key_exists($name, $this->keyProperties)) {
            $this->keyProperties[$name] = $value;
        }
    }

    public function copy(DataClassBase $copyTo, bool $strict = false): void{
       
        foreach(array_keys($copyTo->keyProperties) as $key){
            if(!key_exists($key, $this->keyProperties)){
                if($strict){
                    throw new DataProcessingException("Property $key not found in source object", DataProcessingExceptionCode::PROPERTY_MISSING);
                }
                continue;
            }else{
                $copyTo->{$key} = $this->{$key};
                $copyTo->keyProperties[$key]  = $this->{$key};
            }
        }
    }

    public function loadFromArray(array $data, bool $strict = false): void{
        foreach($data as $key => $value){
            if(!property_exists($this, $key)){
                if($strict){
                    throw new DataProcessingException("Property $key not found in source object", DataProcessingExceptionCode::PROPERTY_MISSING);
                }else{
                    continue;
                }
            }
            $this->{$key} = $value;
        }
    }
}
