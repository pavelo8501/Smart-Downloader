<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use SmartDownloader\Enumerators\RateExceedAction;
use SmartDownloader\Models\SDConfiguration;
use SmartDownloader\SmartDownloader;

class FilesController extends Controller
{

    private SmartDownloader $downloadManager;

    public function index()
    {
        return response()->json([
            'message' => 'Hello, Api Index',
            'status' => 'success'
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'action' => 'required|string',
            'file_url' => 'required|file'
        ]);

        $downloadManage = new SmartDownloader(function (SDConfiguration $config) {
            $config->temp_dir = "temp";
            $config->download_dir = "downloads";
            $config->rate_exceed_action= RateExceedAction::QUE;
            $config->max_downloads = 5;
        });

        $downloadManage->processRequest($$request);

    }
}
