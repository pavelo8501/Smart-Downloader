<?php


namespace SmartDownloader\Services\DownloadService\Models;

use SmartDownloader\Handlers\DataClassBase;

class DownloadDataClass  extends DataClassBase {

    protected int $id = 0;
    protected int $bytes_start = 0;
    protected int $bytes_transferred = 0;
    protected int $bytes_max = 0;
    protected int $chunk_size = 0;
 
    public int $bytes_read_to = 0;
    public bool $stop_download = false;
    public mixed $bytes;

    public function initializeFirstRead(){
       $this->bytes_read_to = $this->chunk_size -1;
       $this->stop_download = false;
    }

    public function setNextRead(int $bytes_read, mixed $bytes) {
        $this->bytes = $bytes;
        $this->bytes_transferred += $bytes_read;
        if($bytes_read < $this->chunk_size){
            $this->stop_download = true;
        }
        if($this->bytes_max < ($this->bytes_transferred +  $this->chunk_size)){
            $this->stop_download = true;
        }
        
        $this->bytes_start += $bytes_read;
        $this->bytes_read_to = $this->bytes_start + $this->chunk_size - 1;
    }

}
