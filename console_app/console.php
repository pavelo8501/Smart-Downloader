/** console.php **/
#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Console\FileDownloadCommand;
use Console\Service\TestService;
use Dotenv\Dotenv;
use SmartDownloader\Services\DownloadService\FileDownloadService;
use SmartDownloader\SmartDownloader;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


$env_path = __DIR__. "../";
$dotenv = Dotenv::createImmutable($env_path);
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$containerBuilder->register(SmartDownloader::class, SmartDownloader::class);
$containerBuilder->register(FileDownloadService::class, FileDownloadService::class);

$containerBuilder->register(TestService::class, TestService::class)
    ->setPublic(true)
    ->addTag('console.command');

$containerBuilder->register(FileDownloadCommand::class, FileDownloadCommand::class)
    ->addArgument(new Reference(TestService::class))
    ->addArgument(new Reference(SmartDownloader::class))
    ->addTag('console.command')
    ->setPublic(true);

$containerBuilder->compile(true);

$app = new Application('Console App', 'v1.0.0');

$fileDownloadCommand = $containerBuilder->get(FileDownloadCommand::class);

$app->add($fileDownloadCommand);

$app -> run();