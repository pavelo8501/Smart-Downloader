<?php

namespace SmartDownloader\Models;

use SmartDownloader\Handlers\DataClassBase;

class DownloadRequest extends DataClassBase{

    public string $file_url;
    public string $request_url;
    public string $file_path;

    public function __construct(
        string $file_url = "",
        string $request_url = "",
        string $file_path = ""
    ){
        $this->file_url = $file_url;
        $this->request_url = $request_url;
        $this->file_path = $file_path;

        parent::__construct(
            "file_url",
            "request_url",
            "file_path"
        );
    }
}