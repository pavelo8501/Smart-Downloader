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
    public int $id = 0;

    public string $file_url = "";
    public string $file_path = "";
    public int $chunk_size = 1024;
    public int $bytes_saved = 0;

    public TransactionStatus $status = TransactionStatus::UNINITIALIZED;

    public bool $can_resume = false;

    public array $transactionData=[];

    public int $file_size = 0;

    protected ?DownloadDataClass $downloadDataClass = null;

    protected array $keyProperties = ["id"=> 0 , "file_url" => "", "chunk_size" => "", "file_path" => "", "bytes_saved" => 0 ];

    public function __construct(array $property_values = null){
        if(is_array($property_values)){
            $this->initFromAssociative($property_values);
        }
        parent::__construct(
            $this->keyProperties
        );
    }

    private function addTransactionData(array $data): void{
       array_push($this->transactionData ,$data);
        if($this->file_size == 0 && $data["bytes_read_to"] > 0){
            $this->file_size = $data["bytes_read_to"];
        }
       $this->notifyUpdated($this);
    }

    public function setTransactionData(array $data): void{
        $this->transactionData = $data;
    }

    public function setDownloadDataClass(DownloadDataClass $downloadData)
    {
        $this->downloadDataClass = $downloadData;
        $downloadData->onUpdatedCallback = function (DownloadDataClass $callingDataClass) {
               $this->addTransactionData(DataClassBase::toAssocArray($callingDataClass));
        };
    }


}
