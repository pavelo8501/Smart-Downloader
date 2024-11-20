<?php

declare(strict_types=1);

namespace App;

use Exception;
use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Models\ApiRequest;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$autoload_path = __DIR__ . "/../vendor/autoload.php";

if(file_exists($autoload_path)){
    require($autoload_path);
}


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
    

     $fakeRequest = new ApiRequest();
     $fakeRequest->action = "start";
     $fakeRequest->file_url = "https://storage.googleapis.com/public_test_access_ae/output_60sec.mp4";

     
    $downloadManager->processRequest($fakeRequest);


}catch(Exception $ex){
    $response["err_code"] = 10;
    $response["msg"] =  $ex->getMessage();
    echo json_encode($response);
}

