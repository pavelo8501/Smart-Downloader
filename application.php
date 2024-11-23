<?php

namespace App;

require __DIR__.'/vendor/autoload.php';

use src\FileDownloadCommand;
use Symfony\Component\Console\Application;


$application = new Application();
    $application->add(new FileDownloadCommand());
    $application->run();
