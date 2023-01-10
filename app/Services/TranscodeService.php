<?php

namespace App\Services;

use App\ArvanClient;
use App\Jobs\DownloadWithXargs;
use App\Jobs\RemoveFromArvanAndLocal;
use App\Jobs\SendIntoStreamProvider;
use App\Jobs\UpdateMainServer;
use App\Jobs\UpdateTranscode;
use App\Jobs\UploadFromLinkToS3;
use App\Models\Transaction\Transaction;
use App\Models\Transcode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Exception;

class TranscodeService
{
    use ArvanClient;
    public $arvan_token = null;
    public $arvan_channel_id = null;
    public $node_name = null;

    public function create_new($request)
    {
        $node = $this->select_node();
        $this->arvan_token = $node['apikey'];
        $this->arvan_channel_id = $node['channel_id'];
        $this->node_name = $node['name'];

        $stream_data = $request->stream_data;
        Log::info(json_encode($stream_data));
        $need_download = false;
        if ($stream_data['disk'] == "s3_for_stream_render") {
            $basic_s3_path = $stream_data['user_id'] . "/" . $stream_data['id'] . "/" . "video.mp4";
            $direct_video_url = Storage::disk('s3_for_stream_render')->url($basic_s3_path);
        } else {
            $need_download = true;
            $direct_video_url = $stream_data['creation_meta']['direct_link'];
        }


        //save new into database
        $created_video = Transcode::create([
            'video_url' => $direct_video_url,
            'source_video_id' => $stream_data['id'],
            'title' => $stream_data['title'],
            'description' => $stream_data['creation_meta']['description'],
            'cover_time' => $stream_data['creation_meta']['cover_time'],
            'channel_id' => $this->arvan_channel_id,
            'channel_token' => $this->arvan_token,
            'status' => 'storing',
            'disk' => $stream_data['disk'],
            'user_id' => $stream_data['user_id'],
        ]);
        Log::info("need dl : " . ($need_download == true ? 1 : 0));

        SendIntoStreamProvider::dispatch($created_video, $this->arvan_token, $this->arvan_channel_id, $need_download, $stream_data['creation_meta']['watermark']);
        return $created_video;
    }

    public function check_status(Transcode $transcode)
    {
        $progress_data = [];

        if (empty($transcode?->video_id)) {
            $this->makeTranscoderFail($transcode);
            return false;
        }
        $this->arvan_token = $transcode->channel_token;
        $video_id = $transcode->video_id;
        $url = 'https://napi.arvancloud.ir/vod/2.0/videos/' . $video_id;
        try {
            $response = $this->sendArvanRequest($url, [], 'get');
        } catch (\Exception $e) {
            $this->makeTranscoderFail($transcode);
            return false;
        }

        if ($response === null) {
            //mean video deleted
            $status = 'deleted';
        } elseif ($response === false) {
            //mean we have error
            $status = $transcode->status;
            Log::info('arvan error for video id : ' . $video_id);
        } else {
            //mean ok

            Log::info(json_encode($response));
            $status = $response?->data?->status ?? null;
            var_dump("id: " . $transcode->id . "  = " . $status);
            if ($status === "downloading" || $status === "converting") {
                $job_status = $response->data->job_status_url;
                try {
                    $progress_data = \Http::retry('3', '400')->get($job_status);
                    $progress_data = (json_decode($progress_data, true));
                    $progress_data['percentage'] = $progress_data['progress'];
                    var_dump('progress: ' . $progress_data['percentage']);
                    $transcode->progress = json_encode($progress_data);
                } catch (\Exception $e) {
                    var_dump('cant get progress');
                    $progress_data['percentage'] = 0;
                }

                if ($status === 'converting') {
                    $update_data['filesize'] = $response?->data?->file_info?->general?->size ?? 0;
                    $update_data['duration'] = $response?->data?->file_info?->general?->duration ?? 0;
                    var_dump('size: ' . $update_data['filesize']);
                }
            }
            if ($status === "complete") {
                //mean video ready for transfer
                $transcode->update([
                    'duration' => $response->data->file_info->general->duration,
                ]);
                $update_data['filesize'] = $response?->data?->file_info?->general?->size ?? 0;
                $update_data['duration'] = $response?->data?->file_info?->general?->duration ?? 0;
                $progress_data['percentage'] = '90';

                //upload into s3
                $this->dupload_video_s3($transcode, $response->data->file_info->general->duration);
//                $this->upload_to_s3($transcode);
                $status = 'uploading_into_s3';

            }

        }

        $transcode->status = $status;
        //update main server
        $update_data['status'] = $status;
        $update_data['progress'] = $progress_data;

        UpdateMainServer::dispatch($transcode->source_video_id, $update_data)->onQueue('main_server_updater');
        $transcode->check_try++;
        $transcode->save();

        return $response;
    }


    public function delete(Transcode $transcode)
    {
        if (!empty($transcode?->video_id)) {
            $video_id = $transcode->video_id;
            $url = 'https://napi.arvancloud.ir/vod/2.0/videos/' . $video_id;
            try {
                $this->arvan_token = $transcode->channel_token;
                $response = $this->sendArvanRequest($url, [], 'delete');
            } catch (\Exception $e) {
                $this->makeTranscoderFail($transcode);
                return false;
            }

            if ($response === null) {
                //mean video deleted
                $status = 'deletedFN';
            } elseif ($response === false) {
                //mean we have error
                $status = 'error';
                Log::info('arvan error for video id and can not delete video : ' . $video_id);
            }

            //every thing is ok. so delete from local storage
            $duploadService = new DuploadService();
            $source_video_id = $transcode->source_video_id;
            $user_id = $transcode->user_id;
            $result = $duploadService->removeFiles($user_id, $source_video_id);
            if ($result) {
                $status = 'deleted';
            } else {
                $status = 'errorFN';
            }
        } else {
            $status = 'errorFN';
        }
        $transcode->status = $status;
        //update main server
        $update_data['status'] = $status;

        UpdateMainServer::dispatch($transcode->source_video_id, $update_data)->onQueue('main_server_updater');
        $transcode->save();
        return $response;
    }

    private function makeTranscoderFail(mixed $transcode)
    {
        $transcode->status = 'fail';
        $transcode->save();

        $update_data = [
            'status' => 'fail',
        ];
        UpdateMainServer::dispatch($transcode->source_video_id, $update_data)->onQueue('main_server_updater');

    }

    public function dupload_video_s3(Transcode $transcode, $duration)
    {
        try {
            $duration_in_minute = ((int)$duration / 60);
            $timeout = 90 + round($duration_in_minute * 0.01 * 60, 0);
            DownloadWithXargs::dispatch($transcode, null)->delay(Carbon::now()->addSeconds($timeout));
        } catch (Exception $e) {
            $this->makeTranscoderFail($transcode);
            return false;
        }
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

        //make video ready to play
        $update_data = [
            'status' => 'ready_to_play',
            'disk' => 's3_vod'
        ];
//        UpdateMainServer::dispatch($transcode->source_video_id, $update_data)->onQueue('main_server_updater');


        //
        foreach ($hls_links as $link) {
            //skip some links from bus
            if (Str::endsWith($link, ['tooltip.vtt', 'tooltip.png'])) {
                UploadFromLinkToS3::dispatch($link, $source_video_id, $user_id);
                continue;
            }
            $jobs->push(new UploadFromLinkToS3($link, $source_video_id, $user_id));
        }
        $jobs->push(new UpdateTranscode($transcode, $update_data));
        $update_data['progress'] = "100";
        $jobs->push(new UpdateMainServer($source_video_id, $update_data));
        if ($jobs->count() == 0) {
            throw new \Exception('No jobs found to dispatch.');
        }
        Bus::chain($jobs)->onQueue('default')->dispatch();

        $transcode->update([
            'status' => "uploading_into_s3",
            'check_try' => DB::raw('check_try+1'),
        ]);

        return true;

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
//        $m3u8 = file_get_contents($url);
        $m3u8 = Http::withOptions([
            'synchronous' => true,
        ])->retry(3, 500)
            ->timeout(20)->get($url)->body();
//        dd($m3u8);
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

    private function select_node()
    {
        $arvan_nodes = config('nodes.arvan');
        $node_size = sizeof($arvan_nodes);
        $selected_node = rand(0, $node_size - 1);
        $selected_node = 0;
        return $arvan_nodes[$selected_node];
    }

}
