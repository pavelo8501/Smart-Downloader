<?php


namespace SmartDownloader\Services\DownloadService\Models;

use SmartDownloader\Exceptions\OperationsException;
use SmartDownloader\Exceptions\OperationsExceptionCode;
use SmartDownloader\Handlers\DataClassBase;

class DownloadDataClass  extends DataClassBase {

    protected int $id = 0;

    public string $file_url = "";
    public int $bytes_start = 0;
    public int $bytes_transferred = 0;
    public int $bytes_max = 0;
    public int $chunk_size = 0;
 
    public int $bytes_read_to = 0;
    public bool $stop_download = false;
    public mixed $bytes;

    public int $max_retries = 0;

    public string $finalizationMessage="";

    public int $retry_await_time = 10;

    private int $bytes_left = 0;


    protected array $keyProperties = ["id"=>0, "bytes_start" =>0, "bytes_read_to" => 0, "chunk_size"=>0, "bytes_max" => 0, "file_url"=>""];

    protected array $errors = [];

    public function __construct() {

        parent::__construct(
            $this->keyProperties
        );
    }

    public function initializeFirstRead(){
       $this->bytes_read_to = $this->chunk_size -1;
       $this->stop_download = false;
    }

    public function setNextRead(int $bytes_read, mixed $bytes) {
        $this->bytes = $bytes;
        $this->bytes_transferred += $bytes_read;
        
        if($bytes_read < $this->chunk_size){
            $this->stop_download = true;
            $this->finalizationMessage = "Finalized by bytes_read < chunk_size";
        }

        if($this->bytes_max > 0){
            $this->bytes_left =  $this->bytes_max - $this->bytes_transferred;
            if($this->chunk_size > $this->bytes_left){
                $this->bytes_start += $bytes_read;
                $this->bytes_read_to = $this->bytes_start + $this->bytes_left - 1;
                return;
            }
        }

        if(!$this->stop_download){
            $this->bytes_start += $bytes_read;
            $this->bytes_read_to = $this->bytes_start + $this->chunk_size - 1;
            $this->notifyUpdated($this);
        }
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
