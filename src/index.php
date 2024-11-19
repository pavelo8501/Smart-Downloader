<?php

declare(strict_types=1);

namespace App;

use Exception;
use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\SmartDownloader;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require("./../vendor/autoload.php");

$downloadManager = new SmartDownloader(function (SDConfiguration $config) {
    $config->temp_dir = "temp";
    $config->download_dir = "downloads";
    $config->rate_exceed_action= RateExceedAction::QUE;
    $config->max_downloads = 5;
});


try {

    $post_request;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $post_input = file_get_contents('php://input');
        if(!$post_input){
            $response["err_code"] = 10;
            $response["msg"] =  $ex->getMessage();
            echo json_encode($response);
            die;
        }
        $post_request = json_decode($post_input, true);
    }

    $downloadManager->processRequest($post_request);


}catch(Exception $ex){
    $response["err_code"] = 10;
    $response["msg"] =  $ex->getMessage();
    echo json_encode($response);
}

