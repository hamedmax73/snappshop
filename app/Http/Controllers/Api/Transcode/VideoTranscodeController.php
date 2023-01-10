<?php

namespace App\Http\Controllers\Api\Transcode;

use App\Http\Controllers\Controller;
use App\Models\Transcode;
use App\Services\TranscodeService;
use AWS\CRT\HTTP\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class VideoTranscodeController extends Controller
{
    public function __construct(public TranscodeService $transcodeService)
    {

    }

    public function store(Request $request)
    {
        if (empty($request->stream_data)) {
            $response = [
                'success' => false,
                'message' => 'bad request',
                'code' => 4401
            ];
            return response($response, 401);
        }
        return $this->transcodeService->create_new($request);
    }

    public function delete(Request $request)
    {

        $streamSourceId = $request->stream_id;
        $transcode = Transcode::where('source_video_id', $streamSourceId)->first();
        if (empty($transcode)) {
            $response = [
                'success' => false,
                'message' => 'can not find this video',
                'code' => 4405
            ];
            return response($response, 404);
        }
        if ($transcode->status == "deleted") {
            $response = [
                'success' => true,
                'message' => 'this video deleted before',
                'code' => 4410
            ];
            return response($response, 404);
        }
        try {
            return $this->transcodeService->delete($transcode);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'error in delete file',
                'code' => -450
            ];
            return response($response, 500);
        }
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
