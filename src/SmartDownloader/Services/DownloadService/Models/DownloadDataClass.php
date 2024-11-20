<?php


namespace SmartDownloader\Services\DownloadService\Models;

use SmartDownloader\Handlers\DataClassBase;

class DownloadDataClass  extends DataClassBase {

    protected int $id = 0;
    protected int $bytes_start = 0;
    protected int $bytes_transferred = 0;
    protected int $bytes_max = 0;
    protected int $chunk_size = 0;
 

}
