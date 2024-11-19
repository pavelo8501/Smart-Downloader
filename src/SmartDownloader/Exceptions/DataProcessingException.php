<?php

namespace SmartDownloader\Exceptions;

use Exception;

enum DataProcessingExceptionCode : int {
    case UNDEFINED  = 0;
    case NO_PARAMS  = 1;
    case PROPERTY_MISSING = 2;
    case NO_PROPERTY_BY_VALUE = 3;
    case PARENT_INIT_FAILED = 4;
}


class DataProcessingException extends Exception{

    private DataProcessingExceptionCode  $exceptionCode = DataProcessingExceptionCode::UNDEFINED;
    private string $exceptionMessage;

    public function __construct(string $exceptionMessage, ?DataProcessingExceptionCode $code, \Throwable $previous = null ) {
        $this->exceptionMessage = $exceptionMessage;
        if($code !== null){
            $this->exceptionCode = $code;
        }

        parent::__construct($this->exceptionMessage, $this->exceptionCode, $previous);
    }
}