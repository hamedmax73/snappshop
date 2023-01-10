<?php

namespace App\Jobs;

use App\Models\Transcode;
use App\Services\TranscodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class RemoveFromArvanAndLocal implements ShouldQueue
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $transcodeService = new TranscodeService();
            return $transcodeService->delete($this->transcode);
        }catch (\Exception $e){
            Log::critical('we can not remove some video after success:' . json_encode($e->getMessage()));
        }
    }
}
