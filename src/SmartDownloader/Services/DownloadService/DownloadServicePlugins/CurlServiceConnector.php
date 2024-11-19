<?php

namespace SmartDownloader\Services\DownloadService\DownloadServicePlugins;

use CurlHandle;
use Exception;
use SmartDownloader\Services\DownloadService\DownloadConnectorInterface;
use SmartDownloader\Handlers\DataClassBase;



 class RequestDataClass extends DataClassBase{
    public bool $isMultipart;
    public int $length;
    public CurlHandle $ch;
 }

 

class CurlServiceConnector implements DownloadConnectorInterface{

  private $ch;
  private string $url;

  public function __construct(){ }

  
  public function isMultipart(string  $url): RequestDataClass {

    $requestResult = new RequestDataClass();
    $this->url = $url;

    $this->ch = curl_init($this->url);
    
    if($this->ch){

      $headers = array();
      curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->ch, CURLOPT_RANGE , true);
      curl_setopt($this->ch, CURLOPT_HEADER, true);
      curl_setopt($this->ch, CURLOPT_FILETIME, true);
      curl_setopt($this->ch, CURLOPT_NOBODY, true);

      $headers =  curl_exec($this->ch);
      if(curl_errno($this->ch)) {
        throw new \Exception(curl_error($this->ch));
      }

      // foreach($headers as $header){

      //     $range = $header['Accept-Ranges']?? null;

      // }

          

     // $requestResult->isMultipart = $range ? true : false;
     // $requestResult->length =  $content_Lengt? $content_Lengt : 0; 
     // $requestResult->ch = $this->ch;
      
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

   private function downloadMultipart(CurlHandle $ch, int $chunk_size, $handleProgress): void{
      echo "Downloading in parts\n";
   }


  public function downloadFile(string $url, int $chunk_size, callable $handleProgress): void{


    $this->isMultipart($url)->isMultipart ? 
      $this->downloadMultipart($this->ch, $chunk_size, $handleProgress) : 
        $this->downloadSingle($url, $chunk_size, $handleProgress);
  }

}

