use PHPUnit\Framework\TestCase;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;

<?php


use PHPUnit\Framework\TestCase;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;



class TransactionDataClassTest extends TestCase {
    
    public function testInitialValues() {
        $transaction = new TransactionDataClass();

        $this->assertEquals(0, $transaction->id);
        $this->assertEquals("", $transaction->url);
        $this->assertEquals(1024, $transaction->chunk_size);
        $this->assertEquals(0, $transaction->bytes_saved);
        $this->assertEquals(TransactionStatus::UNINITIALIZED, $transaction->status);
    }

    public function testPropertiesArrayInitialized() {
        $transaction = new TransactionDataClass();
        $this->assertArrayHasKey("id", $transaction->getProperties());
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


}
