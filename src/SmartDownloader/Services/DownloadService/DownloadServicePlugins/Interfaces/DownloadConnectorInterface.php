<?php


namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces;


use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

interface DownloadConnectorInterface {

    /**
     * Downloads a file from the given URL.
     *
     * @param string $url The URL of the file to download.
     * @param DownloadDataClass $download_data An instance of DownloadDataClass containing download data.
     * @param callable $reportStatusCallback A callback function to report the status of the download.
     * @param callable $handleProgress A callback function to handle the progress of the download.
     *
     * @return void
     */
    public function downloadFile(string $url, DownloadDataClass $download_data, callable $reportStatus, callable $handleProgress): void;
    
    /**
     * Performs a lookup of the headers for the given URL.
     *
     * @param string $url The URL to lookup headers for.
     * @return mixed The headers of the given URL.
     */
    public function headerLookup(string $url);

    /**
     * Stops the download process.
     *
     * @param string $message The message to display when stopping the download.
     * @return void
     */
    public function stopDownload(string $message = ""): void;
}
