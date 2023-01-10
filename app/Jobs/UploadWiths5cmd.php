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
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class UploadWiths5cmd implements ShouldQueue
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
    public function __construct(public Transcode $transcode)
    {
        //
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
     * @return void
     */
    public function handle()
    {
        $duploadService = new DuploadService();
        $this->queueProgress(0);
        $source_video_id = $this->transcode->source_video_id;
        $user_id = $this->transcode->user_id;
        $result = $duploadService->syncFiles($source_video_id,$user_id);
        if ($result) {
            $this->queueProgress(50);
            $duploadService->removeFiles($user_id,$source_video_id);

            //make video ready to play
            $update_data = [
                'status' => 'ready_to_play',
                'disk' => 's3_vod'
            ];
            UpdateMainServer::dispatch($this->transcode->source_video_id, $update_data)->onQueue('main_server_updater');
            $this->queueProgress(100);
            $this->runCleanerAfterSuccess();
            return true;
        }
        $this->fail($result);
        return false;
    }

    /**
     * remove video after completely success. we wait two day for safety
     * @return void
     */
    public function runCleanerAfterSuccess(): void
    {
        try {
            $timeout = 48;
            RemoveFromArvanAndLocal::dispatch($this->transcode, null)->delay(Carbon::now()->addHours($timeout));
        } catch (Exception $e) {
            Log::critical('we can not remove some video after success:' . $this->transcode->id);
        }
    }
}
