<?php

namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins\Plugin;

use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces\DownloadConnectorInterface;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\LoggingService\LoggingService;
use CurlHandle;
use CurlMultiHandle;


class CurlAsync implements DownloadConnectorInterface
{

    protected $curl_handles = [];
    protected CurlMultiHandle $multiHandle;

    public function __construct(public $file_url, public $file_path)
    {
        $this->multiHandle = curl_multi_init();
        $this->curl_handles = [];
    }


    public function retryLoop(CurlHandle $ch, DownloadDataClass $data_reader): void
    {
        $data_reader->setError("");
        curl_multi_remove_handle($this->multiHandle, $ch);
        curl_multi_add_handle($this->multiHandle, $ch);
    }

    public function readHeader(DownloadDataClass $data_reader): CurlHandle|DataProcessingException
    {
        $fileName = basename(parse_url($this->file_url, PHP_URL_PATH));
        $filePath = $this->file_path . '/' . $fileName;
        $file = fopen($filePath, 'a');
        $file_size = filesize($filePath);

        $ch = curl_init($this->file_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($file_size > 0) {
            $data_reader->bytes_read_to = $file_size;
            curl_setopt($ch, CURLOPT_RANGE, $file_size . '-');
            echo "Resuming download of {$fileName} from byte {$file_size}\n";
        } else {
            echo "Starting download of {$fileName}\n";
        }
        curl_multi_add_handle($this->multiHandle, $ch);
        return $ch;
    }


    public function readFile(\CurlHandle $ch, string $file_url, DownloadDataClass $data_reader): bool|OperationsExceptionCode{
        do {
            $status = curl_multi_exec($this->multiHandle, $running);
            if ($status > 0) {
                echo "cURL error: " . curl_multi_strerror($status) . "\n";
            }
            curl_multi_select($this->multiHandle);

            while ($info = curl_multi_info_read($this->multiHandle)) {
                $file_url = $info['handle'];
                $file_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                $data = $this->multiHandle[$file_url];
                $file = $data['file'];

                if ($info['result'] !== CURLE_OK) {
                    $error = curl_error($ch);
                    echo "Error downloading {$file_url}: {$error}\n";
                    LoggingService::error("Error downloading {$file_url}: {$error}");
                    $data_reader->setError($error);
                    return false;
                }
            }
        } while ($data_reader->stop_download = false);
        return true;
    }


    public function freeResources($ch, $file, $data_reader){
        curl_multi_remove_handle($this->multiHandle, $ch, $data_reader);
        curl_close($ch);
        fclose($file);
        unset($this->curl_handles[$data_reader->file_url], $this->curl_handles[$file]);
    }

    public function initializeDownload(callable $connector_configuration):bool | OperationsException{
        $connector_configuration($this);
        return true;
    }
}
