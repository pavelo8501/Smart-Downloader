<?php


namespace SmartDownloader\Services\DownloadService\Models;



final class TransactionDataClass 
{

    public static int $id = 0;
    public static string $url = "";
    public static string $path;
    public static TransactionStatus $status = TransactionStatus::UNINITIALIZED;

    public function __construct() {}
}
