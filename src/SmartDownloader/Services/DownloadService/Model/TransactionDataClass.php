<?php


namespace SmartDownloader\Services\DownloadService\Models;

use SmartDownloader\Handlers\DataClassBase;

final class TransactionDataClass  extends DataClassBase
{

    public static int $id = 0;
    public static string $url = "";
    public static string $path;
    public static TransactionStatus $status = TransactionStatus::UNINITIALIZED;

    public function __construct() {}
}
