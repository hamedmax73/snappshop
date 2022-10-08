<?php

namespace App\Services;

use App\Jobs\UpdateMainServer;
use App\Jobs\UploadFromLinkToS3;
use App\Models\Transaction\Transaction;
use App\Models\Transcode;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TranscodeService
{

    public function __construct(public $arvan_token = null, public $arvan_channel_id = null)
    {
        $this->arvan_token = config('app.arvan_token');
        $this->arvan_channel_id = config('app.arvan_channel_id');
    }


    public function create_new($request)
    {
        $stream_data = $request->stream_data;
        Log::info(json_encode($stream_data));

//        dd('sfsd');
//        $stream_data = json_decode(' {"id":"ee8ce58b-f103-4926-8653-72c1d7959109","user_id":"b4bf2b5f-77e5-4c87-8990-42fb7d054fcc","node_id":null,"parent_id":null,"basename":"1bNaoPZGjuR5.mp4","title":"test for watermakr","original_name":"introamanj","mimetype":"video\/mp4","filesize":"900150","duration":null,"type":"video","disk":"s3_for_stream_render","creation_meta":{"description":"test","cover_time":"3","watermark":"https:\/\/amanjfile.test","watermark_position":["left","bottom"],"watermark_offset":0,"temporary_watermark":false},"converted_for_downloading_at":null,"converted_for_streaming_at":null,"status":"dispatcher","progress":null,"deleted_at":null,"created_at":"2022-10-07T11:13:08.000000Z","updated_at":"2022-10-07T11:13:13.000000Z"}', true);


        //get video url
        if ($stream_data['disk'] == "s3_for_stream_render" || $stream_data['disk'] == "s3") {
            $video_url = $stream_data['user_id'] . '/' . $stream_data['basename'];
            $direct_video_url = Storage::disk('s3_for_stream_render')->url($video_url, now()->addHour());
        }

        //save new into database
        $created_video = Transcode::create([
            'video_url' => $video_url,
            'source_video_id' => $stream_data['id'],
            'title' => $stream_data['title'],
            'description' => $stream_data['creation_meta']['description'],
            'cover_time' => $stream_data['creation_meta']['cover_time'],
            'channel_id' => $this->arvan_channel_id,
            'status' => 'storing',
            'disk' => $stream_data['disk'],
            'user_id' => $stream_data['user_id'],
        ]);


        //create arvan storage data format
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
            'video_url' => $direct_video_url,
        ];

        //create arvan api link
        $store_url = 'https://napi.arvancloud.com/vod/2.0/channels/' . $this->arvan_channel_id . '/videos';

        $response = Http::
        withHeaders([
            'Authorization' => $this->arvan_token
        ])
            ->accept('application/json')
            ->post($store_url, $arvan_data);

        Log::info("sdasdas" . $response);
        $response = json_decode($response);
        //update created video
        $created_video->update([
            'video_id' => $response->data->id,
            'status' => 'added_to_queue'
        ]);

        //update main server
        $update_data = [
            'status' => 'added_to_queue',
        ];
        UpdateMainServer::dispatch($created_video->source_video_id, $update_data)->onQueue('main_server_updater');


        dd($response);
        return $response;
    }

    public function check_status(Transcode $transcode)
    {
        $video_id = $transcode->video_id;

        if(empty($video_id)){
            return "video not added";
        }
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
                'check_try' => DB::raw('check_try+1'),
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
        $source_video_id = $transcode->source_video_id;
        $user_id = $transcode->user_id;
        $hls_links = $this->read_hls($transcode->hls_playlist);
        $jobs = collect();
        foreach ($hls_links as $link) {
            $jobs->push(new UploadFromLinkToS3($link,$source_video_id,$user_id));
        }
        if ($jobs->count() == 0) {
            throw new \Exception('No jobs found to dispatch.');
        }
        Bus::chain($jobs)->onQueue('default')->dispatch();

        $transcode->update([
            'status' => "uploading_into_s3",
            'check_try' => DB::raw('check_try+1'),
        ]);

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
