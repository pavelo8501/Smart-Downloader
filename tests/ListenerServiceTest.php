<?php

use SmartDownloader\Services\ListenerService\ListenerService;
use PHPUnit\Framework\TestCase;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\SmartDownloader;

class ListenerServiceTest extends TestCase {

    private ListenerService $listenerService;
    private SmartDownloader $mockSmartDownloader;
    private SDConfiguration $mockConfig;

    protected function setUp(): void {
        
        $this->mockSmartDownloader = $this->createMock(SmartDownloader::class);
        $this->mockConfig = $this->createMock(SDConfiguration::class);
        $this->listenerService = new ListenerService($this->mockSmartDownloader, $this->mockConfig);
    }

    public function testSubscribeTasksInitiated() {
        $callback = function ($task, $transaction) {
            echo "Task initiated: " . $task . " with transaction: " . $transaction;
        };

        $this->listenerService->subscribeTasksInitaiated($callback);

        $reflection = new ReflectionClass($this->listenerService);
        $property = $reflection->getProperty('onTaskInitiated');
        $property->setAccessible(true);
        $this->assertInstanceOf(Closure::class, $property->getValue($this->listenerService));
    }
}
