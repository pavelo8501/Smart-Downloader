<?php

use PHPUnit\Framework\TestCase;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

class DataContainerTest extends TestCase
{
    public function testRegisterNew()
    {
        $dataContainer = new DataContainer();
        $downloadRequest = $this->createMock(DownloadRequest::class);
        $downloadRequest->method('copy')->willReturn(true);

        $transaction = $dataContainer->registerNew($downloadRequest);

        $this->assertInstanceOf(TransactionDataClass::class, $transaction);
    }

    public function testRemove()
    {
        $dataContainer = new DataContainer();
        $transaction = $this->createMock(TransactionDataClass::class);

        $dataContainer->registerNew($this->createMock(DownloadRequest::class));
        $result = $dataContainer->remove($transaction);

        $this->assertTrue($result);
    }

    public function testGetCountByPropType()
    {
        $dataContainer = new DataContainer();
        $downloadRequest = $this->createMock(DownloadRequest::class);
        $dataContainer->registerNew($downloadRequest);

        $count = $dataContainer->getCountByPropType(TransactionDataClass::class);

        $this->assertEquals(1, $count);
    }

    public function testGetByValue()
    {
        $dataContainer = new DataContainer();
        $downloadRequest = $this->createMock(DownloadRequest::class);
        $transaction = $dataContainer->registerNew($downloadRequest);

        $transaction->getProperties()['testProperty'] = (object)['value' => 'testValue'];

        $result = $dataContainer->getByValue('testProperty', 'testValue');
        $this->assertSame($transaction, $result);

        //$result = $dataContainer->getByValue(TransactionDataClass::$status, TransactionStatus::UNINITIALIZED);

    }

    public function testSubscribeUpdates()
    {
        $dataContainer = new DataContainer();
       // $transaction = $this->createMock(TransactionDataClass::class);

        $transaction =  $dataContainer->registerNew($this->createMock(DownloadRequest::class));

        $callbackCalled = false;
        $dataContainer->subscribeUpdates(function ($updatedTransaction) use (&$callbackCalled, $transaction) {
            $callbackCalled = true;
            $this->assertSame($transaction, $updatedTransaction);
        });

        $transaction->notifyUpdated();

    
       // $dataContainer->onTransactionUpdated($transaction);

        $this->assertTrue($callbackCalled);
    }
}
