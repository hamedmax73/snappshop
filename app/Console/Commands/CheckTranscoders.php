<?php

namespace App\Console\Commands;

use App\ArvanClient;
use App\Jobs\UpdateMainServer;
use App\Models\Transcode;
use App\Services\TranscodeService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckTranscoders extends Command
{
    use ArvanClient;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcoders:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check transcoders status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(TranscodeService $transcodeService)
    {
        $progress_data = [];
        $update_data = [];
        $transcoders = Transcode::whereNotIn('status', ['complete', 'fail','downloading_fail', 'uploading_into_s3', 'deleted'])->where([
            ['check_try', "<", '40'],
        ])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        foreach ($transcoders as $transcoder) {
            $transcodeService->check_status($transcoder);
        }

    }


}
