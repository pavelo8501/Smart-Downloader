<?php

namespace SmartDownloader\Services\DownloadService;

use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Services\DownloadService\DownloadConnectorInterface;

class FileDownloadService {

    // private SmartDownloader $parentService;


    private $connectorPlugin;

    public function __construct(?DownloadConnectorInterface $connectorPlugin = null) {
        if($connectorPlugin != null){
            $this->connectorPlugin = $connectorPlugin;
        }
        // $this->parentService = $parentService;
    }

    //content-range
    //content-length: 1024
    // Accept: */*
    //Range: bytes=0-1023

    public function handleProgress(int $bytesStarted, int $bytesTransferred, int $bytesMax): void {
        echo "Download progress: {$bytesTransferred}/{$bytesMax} bytes\n";
    }


    public function fakeConectionResponse(DownloadRequest $request): DownloadRequest {
        $request->url = "someUrl";
        $request->requestUrl = 'localhost:4200//api/requests';
        return $request;
    }


    public function initializeDownload(DownloadRequest $request): DownloadRequest {
    
        return $this->fakeConectionResponse($request);


        // $this->connectorPlugin->downloadFile(
        //     $url,
        //     1024,
        //     [$this, 'handleProgress']
        // );
        
    }
}
