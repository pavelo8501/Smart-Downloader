<?php

namespace SmartDownloader\Models;

use SmartDownloader\Handlers\DataClassBase;

class DownloadRequest extends DataClassBase{

    public string $url;
    public string $requestUrl;
    public string $path;
}