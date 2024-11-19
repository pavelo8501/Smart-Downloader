<?php


namespace SmartDownloader\Services\DownloadService\Models;

use SmartDownloader\Handlers\DataClassBase;

final class DownloadDataClass  extends DataClassBase {

    public static int $id = 0;
    public static int $bytesStarted =0;
    public static int $bytesTransferred;
    public static int $bytesMax = 0;
 
    public function __construct() {
    }
}
