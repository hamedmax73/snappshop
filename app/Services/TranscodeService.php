<?php

namespace App\Services;

use App\Jobs\UploadFromLinkToS3;
use App\Models\Transaction\Transaction;
use App\Models\Transcode;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TranscodeService
{

    public function __construct(public $arvan_token = null, public $arvan_channel_id = null)
    {
        $this->arvan_token = config('app.arvan_token');
        $this->arvan_channel_id = config('app.arvan_channel_id');
    }


    public function create_new($request)
    {
        $created_video = Transcode::create([
            'video_url' => $request->video_url,
            'title' => $request->title,
            'description' => $request->description,
            'cover_time' => $request->cover_time,
            'channel_id' => $this->arvan_channel_id,
            'status' => 'storing',
        ]);

        $arvan_data = [
            'convert_info' => [],
            'convert_mode' => 'auto',
            'coverTime' => [
                'hour' => '0',
                'minute' => '0',
                'second' => $created_video->cover_time
            ],
            'description' => $created_video->description,
            'parallel_convert' => false,
            'thumbnail_time' => $created_video->cover_time,
            'title' => $created_video->title,
            'video_url' => $created_video->video_url,

        ];

        $store_url = 'https://napi.arvancloud.com/vod/2.0/channels/' . $this->arvan_channel_id . '/videos';

        $response = Http::
        withHeaders([
            'Authorization' => $this->arvan_token
        ])
            ->accept('application/json')
            ->post($store_url, $arvan_data);

        $response = json_decode($response);

        //update created video
        $created_video->update([
            'video_id' => $response->data->id,
            'status' => $response->data->status
        ]);
        return $response;
    }

    public function check_status(Transcode $transcode)
    {
        $video_id = $transcode->video_id;
        $check_url = 'https://napi.arvancloud.com/vod/2.0/videos/' . $video_id;
        $response = Http::
        withHeaders([
            'Authorization' => $this->arvan_token
        ])
            ->accept('application/json')
            ->get($check_url);

        $response = json_decode($response);
        if ($response->data->status == "complete") {
            $transcode->update([
                'status' => $response->data->status,
                'duration' => $response->data->file_info->general->duration,
                'hls_playlist' => $response->data->hls_playlist,
                'thumbnail_url' => $response->data->thumbnail_url,
                'tooltip_url' => $response->data->tooltip_url,
            ]);
        } else {
            $transcode->update([
                'status' => $response->data->status,
                'check_try' => DB::raw('check_try+1'),
            ]);
        }

        return $response;
    }

    /**
     * @throws \Exception
     */
    public function upload_to_s3(Transcode $transcode)
    {
        $hls_links =  $this->read_hls($transcode->hls_playlist);

        $jobs = collect();
        foreach ($hls_links as $link) {
            $jobs->push(new UploadFromLinkToS3($link));
        }
        if($jobs->count() == 0) {
            throw new \Exception('No jobs found to dispatch.');
        }
        Bus::chain($jobs)->onQueue('default')->dispatch();

        return "ok";

    }

    /**
     * download file from url into local
     * @param $url
     * @return void
     */
    private function downloadFile($url)
    {
        // maximum execution time in seconds
        set_time_limit(24 * 60 * 60);
        // folder to save downloaded files to. must end with slash
        $destination_folder = 'downloads/';
        $newfname = $destination_folder . basename($url);

        $file = fopen($url, "rb");
        if ($file) {
            $newf = fopen($newfname, "wb");

            if ($newf)
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
        }

        if ($file) {
            fclose($file);
        }

        if ($newf) {
            fclose($newf);
        }
    }


    private function read_m3u8_video_segment($url, $first = false, $number = null)
    {
        $m3u8 = file_get_contents($url);
        if (strlen($m3u8) > 3) {
            $tmp = strrpos($url, '/');
            if ($tmp !== false) {
                $base_url = substr($url, 0, $tmp);
                $tmp_mini = strrpos($base_url, '/');
                $base_url_mini = substr($url, 0, $tmp_mini + 1);

                $array = preg_split('/\s*\R\s*/m', trim($m3u8), NULL, PREG_SPLIT_NO_EMPTY);
                $url2 = array();
                foreach ($array as $line) {
                    $line = trim($line);
                    if (strlen($line) > 2) {
                        if ($line[0] != '#') {
                            $url2[] = $base_url . "/" . $line;
                        }
                    }
                }
                if ($first) {
                    $url2[] = $base_url . "/" . "encryption-f" . $number . ".key";
                }

                return $url2;

            }
        }
        return false;
    }

    private function read_hls($url)
    {
        $result = [];
        $tmp = strrpos($url, '/');
        $base_url = substr($url, 0, $tmp);
        $tmp_mini = strrpos($base_url, '/');
        $base_url_mini = substr($url, 0, $tmp_mini + 1);
        $mfiles = $this->read_m3u8_video_segment($url);
        $loop = 1;
        foreach ($mfiles as $file) {
            $result[] = $file;
            $result = array_merge($result, $this->read_m3u8_video_segment($file, true, $loop));
            $loop++;
        }


        $result[] = $base_url_mini . "tooltip.vtt";
        $result[] = $base_url_mini . "tooltip.png";
        $result[] = $base_url_mini . "thumbnail.png";
        $result[] = $base_url_mini . "origin_config.json";
        $result[] = $url;
        return $result;
//        foreach ($result as $dl) {
//            echo $dl . "<br />";
//            downloadFile($dl);
//        }


    }

}
