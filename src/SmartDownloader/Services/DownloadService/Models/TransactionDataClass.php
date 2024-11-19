<?php


namespace SmartDownloader\Services\DownloadService\Models;

use Closure;
use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;


/**
 * Class TransactionDataClass
 * @package SmartDownloader\Services\DownloadService\Models
 */
final class TransactionDataClass  extends DataClassBase{
    public static int $id = 0;
    public static string $url = "";
    public static string $path;
    public static int $chunk_size = 1024;
    public static int $bytes_saved = 0;
    public static TransactionStatus $status = TransactionStatus::UNINITIALIZED;



    private ?\Closure $onUpdatedCallback = null;

    public function __construct(?callable $onUpdatedCallback = null) {
        if($onUpdatedCallback){
            $this->onUpdatedCallback = Closure::fromCallable($onUpdatedCallback);
        }
    }


    /**
     * Notify that the transaction was updated.
     */
    public function notifyUpdated(){
        if($this->onUpdatedCallback){
            call_user_func($this->onUpdatedCallback, $this);
        }
    }
}
