<?php

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
Route::group(['prefix' => '/v1'], function (){
    Route::get('/ping', function(){
        return "pong";
    });


    //credit card transfer
    Route::post('/credit/transfer/store',[CreditCardTransferController::class,'store']);


});


//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

