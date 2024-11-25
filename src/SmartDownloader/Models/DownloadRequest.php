<?php

namespace SmartDownloader\Models;

use SmartDownloader\Handlers\DataClassBase;

class DownloadRequest extends DataClassBase{

    public string $file_url= "";
    public string $request_url = "";
    public string $file_path= "";


    protected array $keyProperties = ["file_url" => "", "request_url" => "", "file_path"=> ""];

    public function __construct(){

        parent::__construct($this->keyProperties);
    }
}