<?php

namespace App\Jobs;

use App\Models\Transcode;
use App\Services\DuploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class DownloadWithXargs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public $tries = 1;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Transcode $transcode, public $links)
    {
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new WithoutOverlapping($this->transcode->source_video_id)];
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $duploadService = new DuploadService();
        $this->queueProgress(0);
        $source_video_id = $this->transcode->source_video_id;
        var_dump('setp 1');
        $user_id = $this->transcode->user_id;
        $result = $duploadService->saveInDisk($this->transcode, $source_video_id, $user_id);
        var_dump('step 2');
        $this->queueProgress(50);
        if ($result) {
            $result = $duploadService->downloadFiles($user_id);
            UploadWiths5cmd::dispatch($this->transcode)->delay(Carbon::now()->addSeconds(15));
            $this->queueProgress(100);
            return true;
        }
        $this->fail($result);
        return false;
    }
}
