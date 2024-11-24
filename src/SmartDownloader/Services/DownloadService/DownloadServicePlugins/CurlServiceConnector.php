<?php

namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins;

use Closure;
use CurlHandle;
use CurlMultiHandle;
use Exception;
use SmartDownloader\Exceptions\DataProcessingException;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces\DownloadConnectorInterface;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\LoggingService\LoggingService;
use function PHPUnit\Framework\throwException;


 class RequestDataClass{
    public bool $can_resume = false;
    public string $length = "0";
    public string $ranges = "";
    public ?CurlHandle $ch = null;
 }

 

class CurlServiceConnector implements DownloadConnectorInterface
{

    private $ch;
    private string $url;

    private ?Closure $reportStatusCallback = null;
    private ?Closure $handleProgress = null;

    public bool $download = true;
    public string $interruption_message = "";

    protected DownloadDataClass $download_data;

    protected $data_reader;

    protected $curl_handles = [];
    protected CurlMultiHandle $multiHandle;

    public function __construct(public $file_url, public $file_path){
        $this->multiHandle = curl_multi_init();
        $this->curl_handles = [];
    }

    public function retryLoop(CurlHandle $ch, DownloadDataClass $data_reader):void{
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


    public function readFile(CurlHandle $ch, string $file_url, DownloadDataClass $data_reader): bool|OperationsExceptionCode{
        do {
            $status = curl_multi_exec($this->multiHandle, $running);
            if ($status > 0) {
                echo "cURL error: " . curl_multi_strerror($status) . "\n";
            }

            // Wait for activity on any curl-connection
            curl_multi_select($this->multiHandle);

            // Handle messages
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

        curl_multi_remove_handle($this->multiHandle, $ch,  $data_reader);
        curl_close($ch);
        fclose($file);
        unset($this->curl_handles[$data_reader->file_url], $this->curl_handles[$file]);
    }

  
  public function headerLookup(string  $url): RequestDataClass {

    $requestResult = new RequestDataClass();
    $this->url = $url;
    $this->ch = curl_init($this->url);
    
    if(!$this->ch){
      throw new OperationsException("Using uninitialized download plugin", OperationsExceptionCode::COMPONENT_UNINITIALIZED);
    }

    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_RANGE , true);
    curl_setopt($this->ch, CURLOPT_HEADER, true);
    curl_setopt($this->ch, CURLOPT_FILETIME, true);
    curl_setopt($this->ch, CURLOPT_NOBODY, true);

    $headersResponse =  curl_exec($this->ch);
    if(curl_errno($this->ch)) {
      throw new \Exception(curl_error($this->ch));
    }else{
      curl_close($this->ch);
    }


    $headerLines = explode("\r\n", $headersResponse);

    foreach ($headerLines as $headerLine) {
        if (stripos($headerLine, 'Accept-Ranges:') !== false) {
          $ranges = trim(substr($headerLine, strlen('Accept-Ranges:')));
          $requestResult->can_resume = true;
          $requestResult->ranges = $ranges;
        }
        if (stripos($headerLine, 'Content-Length:') !== false) {
            $requestResult->length = trim(substr($headerLine, strlen('Content-Length:')));
        }
    }
    return $requestResult;
  }

  private function downloadSingle(string $url, int $chunk_size, callable $handleProgress): void{
    
    $context = stream_context_create([
      'http' => [
        'method' => 'GET',
        'header' => "User-Agent: PHP\r\n"
      ]
    ]);

    $stream = fopen($url, 'r', false, $context);

    if ($stream === false) {
      throw new Exception("Failed to open the URL: $url");
    }

    $bytesStarted = 0;
    $bytesTransferred = 0;
    $readOffset = '';

    try {
      while (!feof($stream)) {
        $chunk = fread($stream, $chunk_size);
        if ($chunk === false) {
          throw new Exception("Error reading the stream.");
        }

        $readOffset .= $chunk;
        $bytesTransferred += strlen($chunk);

        $handleProgress($bytesStarted, $bytesTransferred, -1); // Pass -1 for unknown max size
      }
    } finally {
      fclose($stream); // Always close the stream
    }
    LoggingService::event("Download complete. Total bytes: $bytesTransferred");
  }

  private function downloadMultipart(string $url, $handleProgress): void{
     
    $this->ch = curl_init();
    if(!$this->ch){
      throw new OperationsException("Download plugin failed to initialize", OperationsExceptionCode::SOURCE_UNDEFINED);
    }

    $this->download_data->initializeFirstRead();
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array("Content-Type: */*"),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_RANGE => "{$this->download_data->bytes_start}-{$this->download_data->bytes_read_to}"
    ];
    curl_setopt_array($this->ch, $options);
    try{
        do{
          if($this->download_data->stop_download == true || $this->download == false){
            if($this->download == false){
                $this->download_data->finalizeRead($this->interruption_message);
            }
            break;
          }

          $chunk_data =  curl_exec($this->ch);
          $header_info = curl_getinfo($this->ch);
          if (curl_errno($this->ch)) {
            $error_msg = curl_error($this->ch);
            curl_close($this->ch);
            throw new OperationsException($error_msg, OperationsExceptionCode::DOWNLOAD_PLUGIN_FAILURE);
          }
          $bytes_read = strlen($chunk_data);
          $this->download_data->setNextRead($bytes_read, $chunk_data);
          $bytes_start =  $this->download_data->bytes_start;
          $bytes_to = $this->download_data->bytes_read_to;
          $rangeOptions = [CURLOPT_RANGE => "{$bytes_start}-{$bytes_to}"];
          curl_setopt_array($this->ch, $rangeOptions);
          
          if ($this->handleProgress) {
              call_user_func($this->handleProgress, $this->download_data, "start", "Startig download");
          }
        } while($this->download_data->stop_download == false);
    }catch(Exception $e){
      throwException($e);
    }

    if ($this->reportStatusCallback) {
      call_user_func($this->reportStatusCallback, true, "complete", "Bytes transfered {$this->download_data->bytes_transferred}");
    }
  }

  /**
   * Downloads a file from the given URL.
   *
   * @param string $url The URL of the file to download.
   * @param DownloadDataClass $download_data An instance of DownloadDataClass containing download data.
   * @param callable $reportStatus A callback function to report the status of the download.
   * @param callable $handleProgress A callback function to handle the progress of the download.
   *
   * @return void
   */


    public function initializeDownload(callable $connector_configuration):bool | OperationsException{
       $connector_configuration($this);
    }


  public function downloadFile(
    string            $url,
    DownloadDataClass $download_data, 
    callable          $reportStatus,
    callable          $handleProgress): void {

        if(is_callable($reportStatus)){
            $this->reportStatusCallback = Closure::fromCallable($reportStatus);
        }
        if (is_callable($handleProgress)) {
            $this->handleProgress = Closure::fromCallable($handleProgress);
        }
        $headerInfo = $this->headerLookup($url);
        $this->download_data = $download_data;
        $this->download_data->bytes_max = (int)$headerInfo->length;

        if($this->reportStatusCallback){
            call_user_func($this->reportStatusCallback, $headerInfo->can_resume, TransactionStatus::IN_PROGRESS, "Starting download");
        }
        if($headerInfo->can_resume){
            $this->downloadMultipart($url, $this->download_data, $handleProgress);
        }else{
            $this->downloadSingle($url, $download_data->chunk_size, $handleProgress);
        }
  }

  public function stopDownload(string $message = ""): void {
    $this->interruption_message = $message;
    $this->download = false;
    $this->download_data->finalizeRead($message);
  }

}

