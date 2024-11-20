<?php

use PHPUnit\Framework\TestCase;
use SmartDownloader\Services\ListenerService\Models\DataContainer;
use SmartDownloader\Models\DownloadRequest;
use SmartDownloader\Services\DownloadService\Enums\TransactionStatus;
use SmartDownloader\Services\DownloadService\Models\TransactionDataClass;

class DataContainerTest extends TestCase
{

   


    public function testRegisterNew(){
        $dataContainer = new DataContainer();
        $downloadRequest = $this->createMock(DownloadRequest::class);
       
        $transaction = $dataContainer->registerNew($downloadRequest);

        $this->assertInstanceOf(TransactionDataClass::class, $transaction);
    }

    public function testRemove(){
        $dataContainer = new DataContainer();
        
        $transaction = $dataContainer->registerNew($this->createMock(DownloadRequest::class));
        $result = $dataContainer->remove($transaction);

        $this->assertTrue($result);
    }

    public function testGetCountByPropType(){
        $dataContainer = new DataContainer();
        $downloadRequest = $this->createMock(DownloadRequest::class);
        $dataContainer->registerNew($downloadRequest);

        $count = $dataContainer->getCountByPropType("status", TransactionStatus::UNINITIALIZED);

        $this->assertEquals(1, $count);
    }

    public function testGetByValue(){
        $dataContainer = new DataContainer();

        $downloadRequest = new DownloadRequest();
        $downloadRequest->file_url = 'http://test.com';

        $transaction = $dataContainer->registerNew($downloadRequest);

        $result = $dataContainer->getByPropertyValue('file_url', 'http://test.com');
        $this->assertEquals($transaction->file_url, $result);
    }

    public function testSubscribeUpdates(){
        $dataContainer = new DataContainer();

        $transaction =  $dataContainer->registerNew($this->createMock(DownloadRequest::class));

        $callbackCalled = false;
        $dataContainer->subscribeUpdates(function ($updatedTransaction) use (&$callbackCalled, $transaction) {
            $callbackCalled = true;
            $this->assertSame($transaction, $updatedTransaction);
        });

        $transaction->notifyUpdated();
        $this->assertTrue($callbackCalled);
    }


    public function supplyTransactions(): array{

        $transactions = [];
       
        $transaction1 = new TransactionDataClass();
        $transaction1->loadFromArray([
            'id' => 1,
            'url' => 'http://test.com',
            'path' => '/path/to/file',
            'chunk_size' => 2048,
            'bytes_saved' => 1024,
            'status' => TransactionStatus::IN_PROGRESS
        ]);

        $transaction2 = new TransactionDataClass();
        $transaction2->loadFromArray([
            'id' => 2,
            'url' => 'http://test2.com',
            'path' => '/path/to/file2',
            'chunk_size' => 2048,
            'bytes_saved' => 1024,
            'status' => TransactionStatus::IN_PROGRESS
        ]);
        $transactions[] = $transaction1;
        $transactions[] = $transaction2;

        return $transactions;
    }

    public function testRequestData(){

        $callback = [$this, 'supplyTransactions'];
        $dataContainer = new DataContainer($callback);

        $this->assertEquals(2, $dataContainer->getCountByPropType("status", TransactionStatus::IN_PROGRESS));
    }
}
