<?php

namespace Console;

use Console\Service\FileDownService;
use Console\Service\TestService;
use SmartDownloader\SmartDownloader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'fd:download',
    description: 'Starts file download for provided url',
    aliases: ['download'],
    hidden: false,

)]
class FileDownloadCommand extends ConsoleCommand
{
    private TestService $testService;
    private SmartDownloader $fileDownService;

    public function __construct(TestService $testService, SmartDownloader $fileDownService
    ){
        $this->testService = $testService;
        $this->fileDownService = $fileDownService;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'Web url path to download file');
    }
    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $file_url = $input->getArgument('url');
        $command  = ["action"=>"download", "file_url"=>$file_url];

//        $this->fileDownService->logger->subscribe(LogLevel::MESSAGE, function ($message, $timestamp) use ($output) {
//            $output->writeln("{$message} | {$timestamp}");
//        });
//        $this->fileDownService->getRequest($command);

        $output->writeln([

            "ProvidedURL URL: <info>{$file_url}</info>" ,
            $this->testService->getHappyMessage()
        ]);
        return ConsoleCommand::SUCCESS;
    }
}

//$app = new Application();
//$app->add(new HelloWorldCommand());
