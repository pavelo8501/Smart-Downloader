<?php

use PHPUnit\Framework\TestCase;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\Services\DownloadService\DownloadServicePlugins\Interfaces\DownloadConnectorInterface;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

class FileDownloadServiceTest extends TestCase
{
    private $connectorMock;
    private $fileDownloadService;

    private $downloadData;

    protected function setUp(): void{
        $this->connectorMock = $this->createMock(DownloadConnectorInterface::class);
        $this->fileDownloadService = new FileDownloadService($this->connectorMock);
        $this->downloadData = new DownloadDataClass();
    }

    public function testStart_ShouldTriggerDownload(): void{
        $url = "http://example.com/file.txt";
        $chunkSize = 1024;
        $transactionMock = $this->createMock(TransactionDataClass::class);
        $downloadData = new DownloadDataClass();

        $transactionMock->expects($this->once())
            ->method('copy')
            ->with($this->isInstanceOf(DownloadDataClass::class));

        $this->connectorMock->expects($this->once())
            ->method('downloadFile')
            ->with(
                $url,
                $this->isInstanceOf(DownloadDataClass::class),
                $this->callback(function ($callback) {
                    return is_callable($callback);
                }),
                $this->callback(function ($callback) {
                    return is_callable($callback);
                })
            );

        $this->fileDownloadService->start($url, $chunkSize, $transactionMock);
    }

    public function testStop_ShouldCallConnectorStopDownload(): void{
        $message = "Download stopped";

        $this->connectorMock->expects($this->once())
            ->method('stopDownload')
            ->with($message);

        $this->fileDownloadService->stop($message);
    }

    public function testStop_ShouldCallConnectorStopDownload_WithDefaultMessage(): void{
        $this->connectorMock->expects($this->once())
            ->method('stopDownload')
            ->with("");

        $this->fileDownloadService->stop();
    }

    public function testResume_NotImplementedYet(): void{
        $this->expectNotToPerformAssertions();
        $this->fileDownloadService->resume("http://example.com/file.txt", 1024, 100);
    }

    public function testStart_ShouldReportStatusAndHandleProgress(): void{
         $url = "http://example.com/file.txt";
        $chunkSize = 1024;
        $transactionMock = $this->createMock(TransactionDataClass::class);

        $transactionMock->expects($this->once())
            ->method('copy')
            ->with($this->isInstanceOf(DownloadDataClass::class));

        $this->connectorMock->expects($this->once())
            ->method('downloadFile')
            ->willReturnCallback($url, $this->downloadData, function ($reportStatus, $handleProgress) {
                $this->assertIsCallable($reportStatus);
                $this->assertIsCallable($handleProgress);

                
                $reportStatus(true, "complete", "Download completed");
                $handleProgress($this->downloadData);
            });

        $this->fileDownloadService->start($url, $chunkSize, $transactionMock);
    }
}



