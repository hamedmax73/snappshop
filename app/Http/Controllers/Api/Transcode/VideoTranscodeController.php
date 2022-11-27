<?php

namespace App\Http\Controllers\Api\Transcode;

use App\Http\Controllers\Controller;
use App\Models\Transcode;
use App\Services\TranscodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class VideoTranscodeController extends Controller
{
    public function __construct(public TranscodeService $transcodeService)
    {

    }

    public function store(Request $request)
    {
        return $this->transcodeService->create_new($request);
    }

    public function check(Transcode $transcode)
    {
        return $this->transcodeService->check_status($transcode);
    }

    public function upload_to_s3(Transcode $transcode)
    {
        return $this->transcodeService->upload_to_s3($transcode);
    }
}
