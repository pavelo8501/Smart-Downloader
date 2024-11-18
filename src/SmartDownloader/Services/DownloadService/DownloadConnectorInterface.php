<?php


namespace SmartDownloader\Services\DownloadService;


use SmartDownloader\Services\DownloadService;

interface DownloadConnectorInterface {
    public function downloadFile(string $url, int $chunk_size, callable $handleProgress): void;
    public function isMultipart(string  $url);
}
