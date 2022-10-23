<?php

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
    $links = [
        'https://utkarafarini.ir/upload/uploads/timesheetML.pdf',
        'https://utkarafarini.ir/upload/uploads/timesheetSM.pdf'
    ];
	$hamed = "hamed";
    $result = $duploadService->SaveInDisk('dasdasd', $links,$hamed);
    if ($result) {
        $result = $duploadService->downloadFiles($hamed);
        $result2 = $duploadService->syncFiles('dasdasd',$hamed);
        $duploadService->removeFiles($hamed);
        return $result2;
    }
    return "error";
});
