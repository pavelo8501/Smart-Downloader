<?php


namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces;


use CurlHandle;
use CurlMultiHandle;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

interface DownloadConnectorInterface{
    public function retryLoop(CurlHandle $ch, DownloadDataClass $data_reader);

    public function readHeader(DownloadDataClass $data_reader): CurlHandle|DataProcessingException|null;

    public function readFile(CurlHandle $ch, string $file_url, DownloadDataClass $data_reader): bool|OperationsExceptionCode|null;

    public function freeResources($ch, $file, $data_reader);
}
