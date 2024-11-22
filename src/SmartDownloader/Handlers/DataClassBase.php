<?php

namespace SmartDownloader\Handlers;

use ReflectionObject;
use ReflectionProperty;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\DataProcessingExceptionCode;
use SmartDownloader\Handlers\DataHandlerTrait;
use SmartDownloader\Services\LoggingService\LoggingService;

abstract class DataClassBase {



    protected \Closure $onUpdatedCallback;

    public static $keyProperties;

    protected array $fakeProperties;


    public static function toAssocArray(DataClassBase  $object){


       $result[] = [];
       foreach (DataClassBase::class->fakeProperties  as $property){
           $result[$property] = $object->{$property};
       }
       return $result;
    }


    public function __construct(?array $propertiesToExpose = null){
        foreach ($this->exposedProperties as $key => $value){
            self::$keyProperties[][$key] = ["Type" => $value::class, "value" => $value];
            $a =10;
        }
    }
    private array $exposedProperties = [];

    public function execute(array $propertiesToExpose)
    {

        $redundantProperties[]  = array_filter($this->exposedProperties, function ($key) use ($propertiesToExpose) {});

        $newFilteredProperties[] =  array_filter($propertiesToExpose, function ($key)  use ($redundantProperties) {
             !$this->exposedProperties[$key]->value();
        });

        if (empty($newFilteredProperties) && empty($redundantProperties)) {
            return $this;
        }
        $object = new ReflectionObject($this);
        $childClass = new \ReflectionClass($this);

        foreach ($redundantProperties as $redundantProperty) {
            LoggingService::warn("Found redundant property {$redundantProperty} in Data Class");
        }

        foreach ($newProperties as $propertyName) {

            try {

                if(!$propertyName->isPrivate())continue;
                $propertyName->setAccessible(true);
                $property = $childClass->getProperty($propertyName);
                $property->setValue($object, $propertyName);
                $this->exposedProperties[][$propertyName] = $property;

                $a = 10;

//                if (!$property->isPublic()) {
//                    continue;
//                }

//                $getter = function () use ($property) {
//                    return $property->getValue($this);
//                };
//
//                $setter = function ($value) use ($property) {
//                    $property->setValue($this, $value);
//                };
//                $this->exposedProperties[$propertyName] = [
//                    'get' => $getter,
//                    'set' => $setter,
//                ];

            } catch (\ReflectionException $e) {
                throw new DataProcessingException("Property '{$propertyName}' not found in {$childClass->getName()}",
                    DataProcessingExceptionCode::PARENT_INIT_FAILED);
            }
        }
    }
}
