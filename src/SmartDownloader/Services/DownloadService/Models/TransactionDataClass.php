<?php


namespace SmartDownloader\Services\DownloadService\Models;

use Closure;
use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;


/**
 * Class TransactionDataClass
 * @package SmartDownloader\Services\DownloadService\Models
 */
final class TransactionDataClass  extends DataClassBase
{    
    public  int $id = 0;
    public  string $url = "";
    public  string $path = "";
    public  int $chunk_size = 1024;
    public  int $bytes_saved = 0;
    public  TransactionStatus $status = TransactionStatus::UNINITIALIZED;

    private ?\Closure $onUpdatedCallback = null;

    public function __construct(
        ?callable $onUpdatedCallback = null) {

        if($onUpdatedCallback){
            $this->onUpdatedCallback = Closure::fromCallable($onUpdatedCallback);
        }

        parent::__construct(
                "id",
                "url",
                "path",
                "chunk_size",
                "bytes_saved",
                "status",
        );
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
