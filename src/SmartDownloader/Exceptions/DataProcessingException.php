<?php

namespace SmartDownloader\Exceptions;

use Exception;


enum DataExceptionEnum : string
{
    case  NO_PARAMS  = 'No parameters provided for the object';
    case  PROPERTY_MISSING = "Property does not exist in the object";
}

class DataProcessingException extends Exception
{
    public function __construct(DataExceptionEnum $exceptionEnum ,  $code = 0, \Throwable $previous = null ) {
        parent::__construct($exceptionEnum, $code, $previous);
     }
}