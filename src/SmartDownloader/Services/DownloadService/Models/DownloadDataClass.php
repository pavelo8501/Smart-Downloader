<?php


namespace SmartDownloader\Services\DownloadService\Models;

use SmartDownloader\Handlers\DataClassBase;

class DownloadDataClass  extends DataClassBase {

    public  int $id = 0;
    public  int $bytes_started = 0;
    public  int $bytes_transferred = 0;
    public  int $bytesMax = 0;
 
    public function __construct() {
    }
}
