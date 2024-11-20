<?php

namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins;

use Closure;
use CurlHandle;
use Exception;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Services\DownloadService\DownloadConnectorInterface;
use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

 class RequestDataClass{
    public bool $multipart = false;
    public string $length = "";
    public string $ranges = "";
    public ?CurlHandle $ch = null;
 }

 

class CurlServiceConnector implements DownloadConnectorInterface{

  private $ch;
  private string $url;

  private ?Closure $reportStatusCallback = null;
  private ?Closure $handleProgress = null;

  public function __construct(){ }

  
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
          $requestResult->multipart = true;
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
    echo "Download complete. Total bytes: $bytesTransferred";  
  }

  private function downloadMultipart(string $url, DownloadDataClass $download_data, $handleProgress): void{
     
      $this->ch = curl_init();
      if(!$this->ch){
        throw new OperationsException("Download plugin failed to initialize", OperationsExceptionCode::SOURCE_UNDEFINED);
      }

      $options = [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array("Content-Type: */*"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_RANGE => "{$download_data->bytes_start}-{$download_data->chunk_size}"
      ];

      curl_setopt_array($this->ch, $options);
      $chunk_data = curl_exec($this->ch);
      if (curl_errno($this->ch)) {
        $error_msg = curl_error($this->ch);
        curl_close($this->ch);
        if (is_callable($this->reportStatusCallback)) {
            call_user_func($this->reportStatusCallback, true, "error", $error_msg);
        }
      }else{

        $download_data->bytes_transferred = strlen($chunk_data);
        $download_data->bytes_max += $download_data->bytes_transferred;

        if ($this->handleProgress) {
            call_user_func($this->handleProgress, $download_data, "start", "Startig download");
        }
      }
  }


  
  public function downloadFile(
    string $url, 
    DownloadDataClass $download_data, 
    callable $reportStatusCallback, 
    callable $handleProgress): void {

        if(is_callable($reportStatusCallback)){
            $this->reportStatusCallback = Closure::fromCallable($reportStatusCallback);
        }
        if (is_callable($handleProgress)) {
            $this->handleProgress = Closure::fromCallable($handleProgress);
        }

      
        $headerInfo = $this->headerLookup($url);

        if($this->reportStatusCallback){
            call_user_func($this->reportStatusCallback, $headerInfo->multipart, "start", "Startig download");
        }
        if($headerInfo->multipart){
            $this->downloadMultipart($url, $download_data, $handleProgress);
        }else{
            $this->downloadSingle($url, $download_data->chunk_size, $handleProgress);
        }

  }

}

