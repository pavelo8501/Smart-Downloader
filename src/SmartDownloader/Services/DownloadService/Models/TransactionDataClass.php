<?php


namespace SmartDownloader\Services\DownloadService\Models;

use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;

final class TransactionDataClass  extends DataClassBase
{

    public static int $id = 0;
    public static string $url = "";
    public static string $path;
    public static int $chunk_size = 1024;
    public static int $bytes_saved = 0;
    public static TransactionStatus $status = TransactionStatus::UNINITIALIZED;

    public function __construct() {}
}
