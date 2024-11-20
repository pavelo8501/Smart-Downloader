<?php

use PHPUnit\Framework\TestCase;

use SmartDownloader\SmartDownloader;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\CurlServiceConnector;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\ListenerService\ListenerService;

class FileDownloadServiceTest extends TestCase {

    private  FileDownloadService $downloader;

    private ListenerService $listenerService;
    private SmartDownloader $mockSmartDownloader;
    private SDConfiguration $mockConfig;
    private TransactionDataClass $transaction;

    protected function setUp(): void {
        $this->mockSmartDownloader = $this->createMock(SmartDownloader::class);
        $this->mockConfig = $this->createMock(SDConfiguration::class);
        $this->listenerService = new ListenerService($this->mockSmartDownloader, $this->mockConfig);


        $this->downloader = new FileDownloadService(new CurlServiceConnector());

        $this->transaction = new TransactionDataClass();
    }


    public function testHeaderReceived(): void{

         $file_url = "https://storage.googleapis.com/public_test_access_ae/output_60sec.mp4";

        $this->transaction = new TransactionDataClass();
    

        // $downloader = new FileDownloadService(new CurlServiceConnector());
        $this->downloader->start($file_url, 1024, $this->transaction);

    }

}