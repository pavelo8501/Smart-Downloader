<?php


namespace SmartDownloader\Services\DownloadService\Models;

use Closure;
use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Handlers\DataClassBase;

class DownloadDataClass  extends DataClassBase {

    public int $id = 0;

    public string $file_url = "";
    public string $file_path = "";
    public string $file_name = "";
    public int $bytes_start = 0;
    public int $bytes_transferred = 0;
    public int $file_size = 0;
    public int $chunk_size = 0;
 
    public int $bytes_read_to = 0;
    public bool $stop_download = false;
    public mixed $bytes;

    public int $retry_count = 5;

    public string $finalizationMessage="";

    public int $retry_await_time = 10;
    public string $download_dir = "";

    public string $temp_dir = "";

    private int $bytes_left = 0;

    public mixed $file = null;

    public array $keyProperties = ["id"=>0, "bytes_start" =>0, "bytes_read_to" => 0, "chunk_size"=>0, "file_size" => 0,   "file_path"=>"",  "file_url"=>""];

    protected array $messages = [];

    protected array $errors = [];

    public array  $curlHandles= [];

    protected  Closure  $notificationCallback;

    public function notify():array{
        $list = [];
        foreach ($this->messages as $message) {
            $list["messages"] = $message;
        }
        foreach ($this->errors as $error) {
            $list["errors"] = $error;
        }
        return $list;
    }

    public function setNotificationCallback(callable $callback):void{
           $this->notificationCallback = Closure::fromCallable($callback);
    }


    public function __construct() {

        parent::__construct(
            $this->keyProperties
        );
    }

    public function initializeFirstRead(){
       $this->bytes_read_to = $this->chunk_size -1;
       $this->stop_download = false;
    }

    public  function setStreamData (string $url,  array $data){
        $progress =  $data['progress'] = "Progress for {$url}: {$data['progress']}% ({$data['totalDownloaded']} / {$data['totalDownloadSize']} bytes)";
        $bytes_read_to = $data['totalDownloaded'];

        $this->bytes_read_to  = $data['totalDownloaded'];
        $this->file_size   = $data['totalDownloadSize'];
        $this->setNextRead($bytes_read_to);
    }

    public function setNextRead(int $bytes_read) {
        $this->bytes_transferred += $bytes_read;
        
        if($bytes_read < $this->chunk_size){
            $this->stop_download = true;
            $this->finalizationMessage = "Finalized by bytes_read < chunk_size";
        }

        if($this->file_size > 0){
            $this->bytes_left =  $this->file_size - $this->bytes_transferred;
            if($this->chunk_size > $this->bytes_left){
                $this->bytes_start += $bytes_read;
                $this->bytes_read_to = $this->bytes_start + $this->bytes_left - 1;
                return;
            }
        }

        if(!$this->stop_download){
            $this->bytes_start += $bytes_read;
            $this->bytes_read_to = $this->bytes_start + $this->chunk_size - 1;

            $as_array=["id" =>$this->id, "chunk_size"=>$this->chunk_size,   "bytes_start" => $this->bytes_start, "bytes_read_to" => $this->bytes_read_to];

            $this->notifyUpdated($this);
        }
    }

    public function setInfo(array $info){
        $str = json_encode($info);
        $this->messages[]  = $str;
    }

    public function finalizeRead($message):void{
        $this->stop_download = true;
        $this->finalizationMessage = $message;
    }

    private int $retries_count = 0;

    /**
     * @throws OperationsException
     */
    public function setError(string $error_str){
        $this->errors[] =  $error_str;
        $this->finalizeRead($error_str);
        if($this->retries_count > $this->max_retries){
            foreach ($this->errors as $error){
                $this->finalizationMessage.= $error."\n";
            }
           throw new OperationsException($this->finalizationMessage,OperationsExceptionCode::CONNECTOR_READ_FAILURE);
        }
        $this->retries_count++;
    }
    public function setRetry():void{
        $this->retries_count++;

    }

}
