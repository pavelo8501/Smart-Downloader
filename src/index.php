<?php

declare(strict_types=1);

namespace App;

use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\SmartDownloader;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require("./../vendor/autoload.php");

$downloadManager =  new SmartDownloader(function (SDConfiguration $config) {
    $config->downloadDir = 'downloads';
    $config->rate_Exceed_action = RateExceedAction::QUE;
    $config->maxDownloads = 5;
});


$downloadManager->makeConnection('https://www.google.com', 'https://www.google.com');
