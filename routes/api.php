<?php

use App\Http\Controllers\Api\Transcode\VideoTranscodeController;
use App\Http\Controllers\Api\V1\Report\LastUserReportController;
use App\Http\Controllers\Api\V1\Transfer\CreditCardTransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//test application
Route::group(['prefix' => '/v1'], function () {
    Route::get('/ping', function () {
        return "pong";
    });

    //credit card transfer
    Route::post('/credit/transfer/store', [CreditCardTransferController::class, 'store']);

    //reports
    Route::get('/reports/last_users', [LastUserReportController::class, 'show']);
    Route::post('/video/store', [VideoTranscodeController::class, 'store']);
    Route::get('/video/{transcode:source_video_id}/check', [VideoTranscodeController::class, 'check']);
    Route::get('/video/{transcode:source_video_id}/upload_to_s3', [VideoTranscodeController::class, 'upload_to_s3']);
});


Route::post('/dispatcher/create', [VideoTranscodeController::class,'store']);

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

