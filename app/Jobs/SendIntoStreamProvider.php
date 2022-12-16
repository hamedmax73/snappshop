<?php

namespace App\Jobs;

use App\ArvanClient;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use romanzipp\QueueMonitor\Traits\IsMonitored;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SendIntoStreamProvider implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored, ArvanClient;

    private $main_server;
    public $transcode;
    public $arvan_token;
    public $arvan_channel_id;
    public $need_download;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($transcode, $arvan_token, $arvan_channel_id, $need_download = false)
    {
        $this->main_server = config('app.app_main_server');
        $this->transcode = $transcode;
        $this->arvan_token = $arvan_token;
        $this->arvan_channel_id = $arvan_channel_id;
        $this->need_download = $need_download;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->queueProgress(0);
        if ($this->need_download) {

            //first download real file from link
            $path = base_path() . '/public/temp_video';
            $temp_folder = "temp_" . $this->transcode->user_id;
            $commands = [
                'cd ' . $path,
                'rm -rf ' . $temp_folder,
                'mkdir ' . $temp_folder,
                'cd ' . $temp_folder,
                'mkdir ' . $this->transcode->source_video_id,
                'cd ' . $this->transcode->source_video_id,
                'aria2c -x 16 -s 16 -o video.mp4 ' . $this->transcode->video_url,
            ];
            $command = implode(' && ', $commands);
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(360);
            $process->run(null, ['ENV_VAR_NAME' => 'value']);
            Log::info('dl proccess: ---- / ' . $process->getInput());
            // executes after the command finishes
            if (!$process->isSuccessful()) {
                $update_data = [
                    'status' => 'failed_in_download'
                ];
                $this->transcode->update($update_data);
                UpdateMainServer::dispatch($this->transcode->source_video_id, $update_data)->onQueue('main_server_updater');
                throw new Exception ('failed in download file');
            }

            $video_direct_url = config('app.url') . "/temp_video/" . $temp_folder . "/video.mp4";
        } else {
            $basic_s3_path = $this->transcode->user_id . "/" . $this->transcode->source_video_id . "/" . "video.mp4";
            $video_direct_url = Storage::disk($this->transcode->disk)->url($basic_s3_path);
        }
        $this->queueProgress(50);
        //create arvan storage data format
        $arvan_data = [
            'convert_info' => [],
            'convert_mode' => 'auto',
            'coverTime' => [
                'hour' => '0',
                'minute' => '0',
                'second' => $this->transcode->cover_time
            ],
            'description' => $this->transcode->description,
            'parallel_convert' => false,
            'thumbnail_time' => $this->transcode->cover_time,
            'title' => $this->transcode->title,
            'video_url' => $video_direct_url,
        ];

        //create arvan api link
        $store_url = 'https://napi.arvancloud.ir/vod/2.0/channels/' . $this->arvan_channel_id . '/videos';

        $response = $this->sendArvanRequest($store_url, $arvan_data, 'post');
        Log::info(json_encode($response));
        if (!empty($response)) {
            //update created video
            $this->transcode->update([
                'video_id' => $response->data->id,
                'status' => 'added_to_queue'
            ]);

            //update main server
            $update_data = [
                'status' => 'added_to_queue',
            ];
        } else {
            //update created video
            $this->transcode->update([
                'status' => 'fail'
            ]);

            //update main server
            $update_data = [
                'status' => 'fail',
            ];
            $this->fail();
        }
        $this->queueProgress(100);
        UpdateMainServer::dispatch($this->transcode->source_video_id, $update_data)->onQueue('main_server_updater');
    }
}
