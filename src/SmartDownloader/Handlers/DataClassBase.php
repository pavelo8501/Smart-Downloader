<?php

namespace SmartDownloader\Handlers;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Handlers\DataHandlerTrait;


abstract class DataClassBase{

    use DataHandlerTrait;

    protected $properties = [];

    public static function existsProperty(DataClassBase $dataClass,  string $name): bool {
        return key_exists($name, $dataClass->properties);
    }

    public function __construct(string  ...$propertyNames){
        foreach($propertyNames as $propertyName){
            $this->properties[$propertyName] = null;
        }
    }

    protected function initProperties(array $properties): void {
        $this->properties = $properties;
    }


    public function getProperties(): array {
        return $this->properties;
    }


    // public function __get($name) {
    //     if (self::existsProperty($this, $name)) {
    //         return $this->properties[$name];
    //     }
    //     return null;
    // }

    // public function __set($name, $value) {
    //     if (self::existsProperty($this, $name)) {
    //         $this->properties[$name] = $value;
    //     }
    // }

    //abstract protected function initProperties(): void;

    public function copy(DataClassBase $copyTo, bool $strict = false): void{

        foreach($copyTo->properties as $key => $value){
            if(!key_exists($key, $this->properties)){
                if($strict){
                    throw new DataProcessingException("Property $key not found in source object", DataProcessingExceptionCode::PROPERTY_MISSING);
                }
                continue;
            }
            $this->properties[$key] = $value;
        }
    }

    public function loadFromArray(array $data): void{
        foreach($data as $key => $value){
            if(!key_exists($key, $this->properties)){
                throw new DataProcessingException("Property $key not found in source object", DataProcessingExceptionCode::PROPERTY_MISSING);
            }
            $this->properties[$key] = $value;
        }
    }
}
