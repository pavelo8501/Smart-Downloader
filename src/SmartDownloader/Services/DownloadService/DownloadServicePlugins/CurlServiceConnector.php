<?php

namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins;

use Closure;
use CurlHandle;
use Exception;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces\DownloadConnectorInterface;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

use function PHPUnit\Framework\throwException;

 class RequestDataClass{
    public bool $multipart = false;
    public string $length = "0";
    public string $ranges = "";
    public ?CurlHandle $ch = null;
 }

 

class CurlServiceConnector implements DownloadConnectorInterface{

  private $ch;
  private string $url;

  private ?Closure $reportStatusCallback = null;
  private ?Closure $handleProgress = null;

  public bool $download = true;
  public string $interruption_message = "";

  protected DownloadDataClass $download_data;


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
   * @param callable $reportStatusCallback A callback function to report the status of the download.
   * @param callable $handleProgress A callback function to handle the progress of the download.
   *
   * @return void
   */
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
        $this->download_data = $download_data;
        $this->download_data->bytes_max = (int)$headerInfo->length;

        if($this->reportStatusCallback){
            call_user_func($this->reportStatusCallback, $headerInfo->multipart, "start", "Startig download");
        }
        if($headerInfo->multipart){
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

