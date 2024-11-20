<?php


namespace SmartDownloader\Services\DownloadService\Models;

use Closure;
use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;


/**
 * Class TransactionDataClass
 * @package SmartDownloader\Services\DownloadService\Models
 */
class TransactionDataClass  extends DataClassBase
{
    protected  int $id = 0;
    protected  string $file_url = "";
    protected  string $file_path = "";
    protected  int $chunk_size = 1024;
    protected  int $bytes_saved = 0;
    protected  TransactionStatus $status = TransactionStatus::UNINITIALIZED;

    private ?\Closure $onUpdatedCallback = null;

    public function __construct(
        ?callable $onUpdatedCallback = null) {

        if($onUpdatedCallback){
            $this->onUpdatedCallback = Closure::fromCallable($onUpdatedCallback);
        }
    }

    /**
     * Load data from an array.
     * @param callable $onUpdatedCallback
     */
    public function setOnUpdatedCallback(callable $onUpdatedCallback){
        $this->onUpdatedCallback = Closure::fromCallable($onUpdatedCallback);
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
