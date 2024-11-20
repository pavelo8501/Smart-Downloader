<?php


use App\Http\Controllers\Api\FilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/files',   [FilesController::class, "index"] );
Route::post('/files', [FilesController::class, "store"] );


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
