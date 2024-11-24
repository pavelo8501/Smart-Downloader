<?php


namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces;


use CurlHandle;
use CurlMultiHandle;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

interface DownloadConnectorInterface{
    public function retryLoop(CurlHandle $ch, DownloadDataClass $data_reader);

    public function readAsync (array $urls, DownloadDataClass $data_reader);

    public function readSync(DownloadDataClass $data_reader): CurlHandle|DataProcessingException;
    public function initializeDownload(callable $connector_configuration): null | OperationsException;

}
