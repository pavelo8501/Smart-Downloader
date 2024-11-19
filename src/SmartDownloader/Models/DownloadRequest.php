<?php

namespace SmartDownloader\Models;

use SmartDownloader\Handlers\DataClassBase;

class DownloadRequest extends DataClassBase{

    public string $file_url;
    public string $request_url;
    public string $file_path;
}