<?php


use PHPUnit\Framework\TestCase;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;

class TransactionDataClassTest extends TestCase {

    public function testInitialValues() {
        $transaction = new TransactionDataClass();

        $this->assertEquals(0, $transaction->id);
        $this->assertEquals("", $transaction->file_url);
        $this->assertEquals(1024, $transaction->chunk_size);
        $this->assertEquals(0, $transaction->bytes_saved);
        $this->assertEquals(TransactionStatus::UNINITIALIZED, $transaction->status);
    }

    public function testPropertiesArrayInitialized() {
        $transaction = new TransactionDataClass();
        
        $this->assertObjectHasProperty("id", $transaction);
    }

    public function testNotifyUpdated() {
        $wasCalled = false;
        $callback = function ($transaction) use (&$wasCalled) {
            $wasCalled = true;
            $this->assertInstanceOf(TransactionDataClass::class, $transaction);
        };

        $transaction = new TransactionDataClass($callback);
        $transaction->notifyUpdated();

        $this->assertTrue($wasCalled);
    }

    public function testDataLooadedFromArray() {

        $transaction = new TransactionDataClass();
        $transaction->loadFromArray([
            'id' => 1,
            'file_url' => 'http://test.com',
            'file_path' => '/path/to/file',
            'chunk_size' => 2048,
            'bytes_saved' => 1024,
            'status' => TransactionStatus::IN_PROGRESS
        ]);

        $this->assertEquals(1, $transaction->id);
        $this->assertEquals('http://test.com', $transaction->file_url);
        $this->assertEquals('/path/to/file', $transaction->file_path);
        $this->assertEquals(2048, $transaction->chunk_size);
        $this->assertEquals(1024, $transaction->bytes_saved);
        $this->assertEquals(TransactionStatus::IN_PROGRESS, $transaction->status);
    }

}
