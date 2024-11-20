<?php

use PHPUnit\Framework\TestCase;
use SmartDownloader\Services\DownloadService\Models\DownloadDataClass;

class DownloadDataClassTest extends TestCase
{
    public function testInitializeFirstRead(){
        $downloadData = new DownloadDataClass();
        $downloadData->chunk_size = 1024;
        $downloadData->initializeFirstRead();

        $this->assertEquals(1023, $downloadData->bytes_read_to);
        $this->assertFalse($downloadData->stop_download);
    }

    public function testSetNextRead(){
        $downloadData = new DownloadDataClass();
        $downloadData->chunk_size = 1024;
        $downloadData->bytes_max = 4096;

        $downloadData->setNextRead(1024, 'some bytes');
        $this->assertEquals(1024, $downloadData->bytes_transferred);
        $this->assertEquals(1024, $downloadData->bytes_start);
        $this->assertEquals(2047, $downloadData->bytes_read_to);
        $this->assertFalse($downloadData->stop_download);

        $downloadData->setNextRead(512, 'some more bytes');
        $this->assertEquals(1536, $downloadData->bytes_transferred);
        $this->assertEquals(1536, $downloadData->bytes_start);
        $this->assertEquals(2559, $downloadData->bytes_read_to);
        $this->assertTrue($downloadData->stop_download);
    }

    public function testSetNextReadStopsDownloadWhenBytesReadLessThanChunkSize(){
        $downloadData = new DownloadDataClass();
        $downloadData->chunk_size = 1024;
        $downloadData->bytes_max = 4096;

        $downloadData->setNextRead(512, 'some bytes');
        $this->assertTrue($downloadData->stop_download);
    }

    public function testSetNextReadStopsDownloadWhenBytesMaxExceeded(){
        $downloadData = new DownloadDataClass();
        $downloadData->chunk_size = 1024;
        $downloadData->bytes_max = 2048;

        $downloadData->setNextRead(1024, 'some bytes');
        $this->assertFalse($downloadData->stop_download);

        $downloadData->setNextRead(1024, 'some more bytes');
        $this->assertTrue($downloadData->stop_download);
    }
}