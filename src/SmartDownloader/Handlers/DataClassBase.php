<?php

namespace SmartDownloader\Handlers;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Handlers\DataHandlerTrait;


abstract class DataClassBase{

    use DataHandlerTrait;

    public $properties = [];

    public function __construct(mixed ...$values){
        $this->properties = $values;
    }

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
