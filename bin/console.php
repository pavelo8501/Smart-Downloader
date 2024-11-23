<?php

namespace App\FileDownloader;

require __DIR__ . '/../vendor/autoload.php';


use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:file_download',
    description: 'Starts file download for provided url',
    hidden: false,
)]
class FileDownloadCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'Web url path to download file');
    }


    protected function execute(InputInterface $input, OutputInterface $output):int
    {

        $file_url = $input->getArgument('url');


        $output->writeln([

            "ProvidedURL URL: <info>{$file_url}</info>",

        ]);

        return Command::SUCCESS;
    }
}

$app = new Application();
$app->add(new FileDownloadCommand());

$app->run();