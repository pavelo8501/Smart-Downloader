<?php

namespace SmartDownloader\Services\DownloadService;

use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Services\DownloadService\DownloadConnectorInterface;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\ListenerService\Models\DataContainer;

class FileDownloadService {

    // private SmartDownloader $parentService;


    private $connectorPlugin;
    private DataContainer $dataContainerInstance;

    public function __construct(
         DataContainer $dataContainerInstance,
        ?DownloadConnectorInterface $connectorPlugin = null
        ) {
            $this->$dataContainerInstance = $dataContainerInstance;
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
        $downloadData = new DownloadDataClass();
        $downloadData->bytesStarted = $bytesStarted;
        $downloadData->bytesTransferred = $bytesTransferred;
        $downloadData->bytesMax = $bytesMax;
    }


    public function fakeConectionResponse(DownloadRequest $request): DownloadRequest {
        $request->url = "someUrl";
        $request->requestUrl = 'localhost:4200//api/requests';
        return $request;
    }


    public function initializeDownload(DownloadRequest $request): DownloadRequest {
    
        $connectionRequest =  $this->fakeConectionResponse($request);
        $downloadData = new DownloadDataClass();

        $this->dataContainerInstance->registerNewConnection(
            $this->fakeConectionResponse($request),
            $downloadData
        );


        // $this->connectorPlugin->downloadFile(
        //     $url,
        //     1024,
        //     [$this, 'handleProgress']
        // );
        return $connectionRequest;
    }
}
