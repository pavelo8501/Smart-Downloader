<?php

namespace SmartDownloader\Exceptions;

use Exception;

enum OperationsExceptionCode: int {
    case UNDEFINED  = 0;
    case SOURCE_UNDEFINED  = 1;
    case TRANSACTION_NOT_FOUND = 2;
    case COMPONENT_UNINITIALIZED = 3;
    case DOWNLOAD_INITIALIZATION_FAIL = 4;
    case DOWNLOAD_PLUGIN_FAILURE = 5;
    case KEY_CALLBACK_UNINITIALIZED = 6;
    case TRANSACTIONS_NOT_LOADED = 7;
    case SETUP_FAILURE = 8;
    case DATASOURCE_CONNECTION_FAILURE = 9;
    case DATASOURCE_INSERT_FAIL = 10;
}


class OperationsException extends Exception {

    private OperationsExceptionCode  $exceptionCode = OperationsExceptionCode::UNDEFINED;
    private string $exceptionMessage;

    public function __construct(string $exceptionMessage, ?OperationsExceptionCode $code, \Throwable $previous = null) {
        $this->exceptionMessage = $exceptionMessage;
        if ($code !== null) {
            $this->exceptionCode = $code;
        }

        parent::__construct($this->exceptionMessage, $this->exceptionCode->value, $previous);
    }
}
