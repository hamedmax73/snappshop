<?php

use App\Jobs\DownloadWithXargs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('jobs')->group(function () {
    Route::queueMonitor();
});
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    $watermark_url =   'https://static.nic.ir/static/images/ipm_logo.fa.png';
    //find watermark

    $url = 'https://napi.arvancloud.ir/vod/2.0/channels/' . "a5f9a49a-5230-40f1-a260-b4aa8f38e2a4" . '/watermarks';
    $arvan_token = config('app.arvan_token');
    return $arvan_token;
    $data=[
        'filter'    => $watermark_url,
    ];

    $client = Http::
    withHeaders([
        'Authorization' => $arvan_token
    ])
        ->retry('3', '400')
        ->timeout('100');
    if (!empty($file)) {
        $client->attach(...$file);
    }
//    $client->contentType('application/x-www-form-urlencoded');
    $response = $client
        ->get($url, $data);
    $result =json_decode($response->body());
    if(isset($result->data[0])){
        return $result->data[0]->id;
    }

    ///save new watermark
    $data = [
        'title' => $watermark_url,
        'description' => Carbon::now(),
    ];
    $file = [
        'watermark',
        file_get_contents($watermark_url),
        'image.png'
    ];
    $method = "POST";
    //create arvan api link
    $url = 'https://napi.arvancloud.ir/vod/2.0/channels/' . "a5f9a49a-5230-40f1-a260-b4aa8f38e2a4" . '/watermarks';

    $arvan_token = config('app.arvan_token');

    $client = Http::
    withHeaders([
        'Authorization' => $arvan_token
    ])
        ->retry('3', '400')
        ->timeout('100');
    if (!empty($file)) {
        $client->attach(...$file);
    }
//    $client->contentType('application/x-www-form-urlencoded');
    $response = $client
        ->post($url, $data);
    $result = json_decode($response->body());
    return $result->data->id;
    dd();
    Log::info("arvan responce: " . $response);
    if ($response->successful()) {
        Log::info('arvan send data resut: ' . json_encode($response));
        return json_decode($response);
    }

    if ($response->clientError()) {
        Log::info("arvan error12 : " . json_encode($response));
        return false;
    }

    if ($response->failed()) {
        Log::info("arvan error16 : " . json_encode($response));
        return false;
    }

//    $arvan_nodes = config('nodes.arvan');
//    $node_size = sizeof($arvan_nodes);
//    $selected_node = rand(0,$node_size-1);
//    return $arvan_nodes[$selected_node]['apikey'];
//    $trans = \App\Models\Transcode::where('source_video_id','6b5eab89-1f1b-4582-b542-416f74083236')->first();
//    $user = $trans->user_id;
//
//    DownloadWithXargs::dispatch($trans, null);
//    $result = $duploadService->saveInDisk($trans,'6b5eab89-1f1b-4582-b542-416f74083236', $user);
//
//    if ($result) {
//        $result = $duploadService->downloadFiles($hamed);
//        $result2 = $duploadService->syncFiles('dasdasd',$hamed);
//        $duploadService->removeFiles($hamed);
//        return $result2;
//    }
    return "error";
});
