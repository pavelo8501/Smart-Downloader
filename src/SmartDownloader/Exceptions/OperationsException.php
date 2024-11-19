<?php

namespace SmartDownloader\Exceptions;

use Exception;

enum OperationsExceptionCode: int {
    case UNDEFINED  = 0;
    case SOURCE_UNDEFINED  = 1;
    case TRANSACTION_NOT_FOUND = 2;
}


class OperationsException extends Exception {

    private DataProcessingExceptionCode  $exceptionCode = OperationsExceptionCode::UNDEFINED;
    private string $exceptionMessage;

    public function __construct(string $exceptionMessage, ?OperationsExceptionCode $code, \Throwable $previous = null) {
        $this->exceptionMessage = $exceptionMessage;
        if ($code !== null) {
            $this->exceptionCode = $code;
        }

        parent::__construct($this->exceptionMessage, $this->exceptionCode, $previous);
    }
}
