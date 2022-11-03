<?php

use App\Jobs\DownloadWithXargs;
use Illuminate\Support\Carbon;
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

Route::get('/test', function (\App\Services\DuploadService $duploadService) {

    $trans = \App\Models\Transcode::where('source_video_id','6b5eab89-1f1b-4582-b542-416f74083236')->first();
    $user = $trans->user_id;

    DownloadWithXargs::dispatch($trans, null);
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
