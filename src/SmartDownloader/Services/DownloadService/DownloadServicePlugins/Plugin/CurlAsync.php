<?php

namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins\Plugin;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces\DownloadConnectorInterface;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\LoggingService\LoggingService;
use CurlHandle;
use CurlMultiHandle;


class CurlAsync implements DownloadConnectorInterface
{

    protected $curl_handles = [];
    protected CurlMultiHandle $multiHandle;

    protected DownloadDataClass $data_reader;

    public function __construct(public $file_url, public $file_path)
    {
        $this->multiHandle = curl_multi_init();
        $this->curl_handles = [];
    }





    public function retryLoop(CurlHandle $ch, DownloadDataClass $data_reader): void{
        $data_reader->setRetry();
        curl_multi_remove_handle($this->multiHandle, $ch);
        curl_multi_add_handle($this->multiHandle, $ch);
    }

    public function readAsync(array $urls, DownloadDataClass $data_reader): void{
        $this->data_reader = $data_reader;
        $downloadDir = $this->file_path;
        if (!is_dir($downloadDir)) {
            mkdir($downloadDir, 0777, true);
        }

        $max_retry_attempts = 5;


        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $progressData = []; // To store progress for each URL

        foreach ($urls as $url) {
            $fileName = basename(parse_url($url, PHP_URL_PATH));
            $file_path = $downloadDir . '/' . $fileName;

            $file = fopen($file_path, 'a');
            $file_size = fileSize($file_path);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_FILE, $file);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            if ($file_size > 0) {
                curl_setopt($ch, CURLOPT_RANGE, $file_size . '-');
                echo "Resuming download of {$fileName} from byte {$file_size}\n";
            } else {
                echo "Starting download of {$fileName}\n";
            }

            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function (
                $resource,
                $downloadSize,
                $downloaded,
                $uploadSize,
                $uploaded
            ) use (&$progressData, $url, $file_size) {
                $totalDownloaded = $file_size + $downloaded;
                $totalDownloadSize = $file_size + $downloadSize;

                if ($downloadSize > 0) {
                    $progress = round($totalDownloaded / $totalDownloadSize * 100, 2);
                    $progressData[$url] = [
                        'totalDownloaded' => $totalDownloaded,
                        'totalDownloadSize' => $totalDownloadSize,
                        'progress' => $progress,
                    ];

                    echo "Downloading {$url}: {$progress}% ({$totalDownloaded} / {$totalDownloadSize} bytes)\r";
                } else {
                    echo "Downloading {$url}: {$downloaded} bytes downloaded\r";
                }
            });

            curl_multi_add_handle($multiHandle, $ch);

            $curlHandles[$url] = [
                'handle' => $ch,
                'file' => $file,
                'file_path' => $file_path,
                'file_size' => $file_size,
                '$retry_attempts' => 5, //
            ];
        }

        $running = null;
        do {

            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);

            if ($data_reader->stop_download == true) {
                echo "Stop command received. Terminating downloads...\n";
                break;
            }

            // Check for errors
            while ($info = curl_multi_info_read($multiHandle)) {
                $ch = $info['handle'];
                $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                $data = $curlHandles[$url];

                if ($info['result'] !== CURLE_OK) {
                    $error = curl_error($ch);
                    echo "\nError downloading {$url}: {$error}\n";

                    // Retry logic
                    if ($data['$retry_attempts'] < $max_retry_attempts) {
                        $data['$retry_attempts']++;
                        echo "Retrying {$url} (Attempt {$data['$retry_attempts']}/{$max_retry_attempts})\n";
                        sleep(30);

                        // Restart the download by re-adding the handle
                        curl_multi_remove_handle($multiHandle, $ch);
                        curl_setopt($ch, CURLOPT_RANGE, $data['file_size'] . '-'); // Resume from the last byte
                        curl_multi_add_handle($multiHandle, $ch);
                    } else {
                        echo "Failed to download {$url} after {$max_retry_attempts} attempts.\n";
                        curl_multi_remove_handle($multiHandle, $ch);
                        fclose($data['file']);
                        unset($curlHandles[$url]);
                    }
                }
            }
            foreach ($progressData as $url => $data) {
                $data_reader->setStreamData($url, $data);
            }
        } while ($running > 0);
        foreach ($curlHandles as $url => $data) {
            $ch = $data['handle'];
            $file = $data['file'];
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
            fclose($file);
        }
        curl_multi_close($multiHandle);
    }


    public function readSync(DownloadDataClass $data_reader): CurlHandle|DataProcessingException{

        $downloadStatus = [];
        $data_reader->setStreamData($data_reader->file_url ,$downloadStatus);

        $fileName = basename(parse_url($data_reader->file_url, PHP_URL_PATH));

        $file_path = $data_reader->file_path . '/' . $fileName;
        $file = fopen($file_path, 'a');
        $data_reader->file_size = fileSize($file);
        $ch = curl_init($file_path);
        $transaction_id = $data_reader->id;
        $file_size = $data_reader->file_size;


        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function (
            $resource,
            $downloadSize,
            $downloaded,
            $uploadSize,
            $uploaded
        ) use ($transaction_id, $file_size, &$downloadStatus) {
            $totalDownloaded = $file_size + $downloaded;
            $totalDownloadSize = $file_size + $downloadSize;

            if ($downloadSize > 0) {
                $downloadStatus['transaction_id'] = $transaction_id;
                $downloadStatus['totalDownloaded'] = $totalDownloaded;
                $downloadStatus['totalDownloadSize'] = $totalDownloadSize;

                $progress = round($totalDownloaded / $totalDownloadSize * 100, 2);
                $downloadStatus['progress'] = $progress;

                echo "Downloading {$transaction_id}: {$progress}% ({$totalDownloaded} / {$totalDownloadSize} bytes)\r";
            } else {
                echo "Downloading {$transaction_id}: {$downloaded} bytes downloaded\r";
            }
        });

        curl_exec($ch);

        if (isset($downloadStatus['progress'])) {
            echo "\nDownload Progress for Transaction {$downloadStatus['transaction_id']}:";
            echo "\nTotal Downloaded: {$downloadStatus['totalDownloaded']} bytes";
            echo "\nTotal Download Size: {$downloadStatus['totalDownloadSize']} bytes";
            echo "\nProgress: {$downloadStatus['progress']}%\n";
        } else {
            echo "\nNo progress information available.\n";
        }
        return $ch;
    }

    public function stopDownload(string $message = ""): void {
        if( $this->data_reader != null){
            $this->data_reader->stop_download = true;
        }
    }

    public function initializeDownload(callable $connector_configuration): null | OperationsException{

          return  $connector_configuration($this);
    }
}
