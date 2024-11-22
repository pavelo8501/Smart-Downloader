<?php


namespace SmartDownloader\Services\DownloadService\Models;

use Closure;
use SmartDownloader\Handlers\DataClassBase;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;


/**
 * Class TransactionDataClass
 * @package SmartDownloader\Services\DownloadService\Models
 */
class TransactionDataClass  extends DataClassBase{
    public  int $id = 0;

    public  string $file_url = "";
    public  string $file_path = "";
    public  int $chunk_size = 1024;
    public  int $bytes_saved = 0;
    
    public  TransactionStatus $status = TransactionStatus::UNINITIALIZED;


    protected ?DownloadDataClass $childTransaction = null;

    public function setChildTransaction(DownloadDataClass $downloadData){

        $this->childTransaction = $downloadData;

    }

    private  array $properties = ["id","file_url","chunk_size","file_path", "bytes_saved"];

    public function __construct(){

        parent::__construct();
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
