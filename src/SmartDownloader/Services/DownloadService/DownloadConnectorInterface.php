<?php


namespace SmartDownloader\Services\DownloadService;


use SmartDownloader\Services\DownloadService;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

interface DownloadConnectorInterface {
    public function downloadFile(string $url, DownloadDataClass $download_data, callable $reportStatus, callable $handleProgress): void;
    public function headerLookup(string  $url);
}
