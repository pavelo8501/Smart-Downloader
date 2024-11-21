<?php

declare(strict_types=1);

namespace App;

use Exception;
use SmartDownloader\SmartDownloader;
use SmartDownloader\Models\ApiRequest;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$autoload_path = __DIR__ . "/../vendor/autoload.php";

if(file_exists($autoload_path)){
    require($autoload_path);

}

    $fakeRequest = new ApiRequest();
    $fakeRequest->action = "start";
    $fakeRequest->file_url = "https://storage.googleapis.com/public_test_access_ae/output_60sec.mp4";

    $downloadManager = new SmartDownloader();
    $downloadManager->getRequest($fakeRequest);

try {

    if ($_SERVER["REQUEST"] == "POST") {
        $post_input = file_get_contents('php://input');
    }
} catch (Exception $ex) {
    $response["err_code"] = 10;
    $response["msg"] =  $ex->getMessage();
    echo json_encode($response);
}


    