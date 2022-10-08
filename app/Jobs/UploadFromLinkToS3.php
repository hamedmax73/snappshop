<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class UploadFromLinkToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use IsMonitored;

    public $tries = 5;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(10);
    }


    public $link_url;
    public $source_video_id;
    public $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($link, $source_video_id, $user_id)
    {
        $this->link_url = $link;
        $this->source_video_id = $source_video_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $diskTo = Storage::disk('s3_arvan');
        $info = pathinfo($this->link_url);
        $filename = $this->user_id . "/" . $this->source_video_id . "/" . $info['basename'];
        $response = Http::withOptions([
            'synchronous' => true,
        ])->retry(3, 5000)->get($this->link_url);
        $diskTo->put(
            $filename,
            $response->getBody()
        );

        $this->queueProgress(100);
    }
}
