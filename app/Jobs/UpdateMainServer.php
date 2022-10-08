<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class UpdateMainServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;
    private $main_server;
    public $video_id;
    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($video_id,$data)
    {
        $this->main_server = config('app.app_main_server');
        $this->video_id = $video_id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::withOptions([
            'debug' => false,
            'verify' => false,
        ])->post($this->main_server. "/api/dispatcher/update_main_server", [
            'video_id' => $this->video_id,
            'data' => $this->data,
        ]);
        return $response;
    }
}
