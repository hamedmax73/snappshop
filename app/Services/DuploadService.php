<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DuploadService
{
    public $video_id;

    /**
     * @return mixed
     */
    public function getVideoId(): mixed
    {
        return $this->video_id;
    }

    /**
     * @param mixed $video_id
     */
    public function setVideoId(mixed $video_id): void
    {
        $this->video_id = $video_id;
    }


    public function linkArrays(): array
    {
        return [
            'https://utkarafarini.ir/upload/uploads/timesheetML.pdf',
            'https://utkarafarini.ir/upload/uploads/timesheetSM.pdf'
        ];
    }

    public function linkText($links)
    {
        return implode("\n", $links);
    }


    private function read_m3u8_video_segment($url, $first = false, $number = null)
    {
        Log::info('22222,' . $url);
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
    }

    public function saveInDisk($transcode, $video_id, $user_id)
    {
        $path = base_path() . '/public/';
        var_dump($user_id);
        $this->setVideoId($video_id);
        $video_id = $this->getVideoId();
        Log::info('transcode: ---- / ' . $transcode->hls_playlist);
        Log::info('path: ---- / ' . $path);
        $hls_links = $this->read_hls($transcode->hls_playlist);
        $URLs = $this->linkText($hls_links);
        $commands = [
            'cd ' . $path,
            'rm -rf ' . $user_id,
            'mkdir ' . $user_id,
            'cd ' . $user_id,
            'mkdir ' . $video_id,
            'cd ' . $video_id,
            'rm -f list.txt',
//            'touch list.txt',
//            'chmod 777 list.txt'
//            'echo -e "' . $URLs . '" > list.txt'
        ];
        $command = implode(' && ', $commands);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(360);
        $process->run(null, ['ENV_VAR_NAME' => 'value']);
        Log::info('proccess: ---- / ' . $process->getInput());
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            return new ProcessFailedException($process);
        }
        //add url into text file
        $fp = fopen($path . $user_id . '/' . $video_id . '/' . "list.txt", "wb");
        fwrite($fp, $URLs);
        fclose($fp);
        return true;
    }

    public function downloadFiles($user_id)
    {
        $path = base_path() . '/public';
        var_dump($path);
        $video_id = $this->getVideoId();
        $commands = [
            'cd ' . $path,
            'cd ' . $user_id,
            'cd ' . $video_id,
            'sudo xargs -n 1 -P 0 curl -s -O < list.txt'
        ];
        $command = implode(';', $commands);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->run(null, ['ENV_VAR_NAME' => 'value']);
        $exit_code = $process->getExitCode();
        var_dump("exit code: " . $exit_code);
        // executes after the command finishes
        if (!in_array($exit_code, [0, 123], false)) {
            throw new ProcessFailedException($process);
        }
        return true;
    }

    public function syncFiles($video_id, $user_id)
    {
        $path = base_path() . '/public';
        $files_directory = $path . "/" . $user_id;
        $temp_video_url = $path . '/' . 'temp_video' .'/'."temp_" . $user_id;
        var_dump($files_directory);

//        $commands = [
//            'sudo /root/s5cmd --endpoint-url=https://s3.ir-thr-at1.arvanstorage.com sync --size-only --exclude "*.m3u8"  --exclude "*.key" --acl "public-read"  ' . $files_directory . '  s3://karbafubuket1',
//            'sudo /root/s5cmd --endpoint-url=https://s3.ir-thr-at1.arvanstorage.com sync --size-only  --acl "private" ' . $files_directory . '  s3://karbafubuket1',
//            "sudo /root/s5cmd --endpoint-url=https://s3.ir-thr-at1.arvanstorage.com sync --size-only " . $temp_video_url . '  s3://karbafubuket1',
//        ];

        //first upload public files
        $commands = [
            'sudo /root/s5cmd --endpoint-url=https://s3.ir-thr-at1.arvanstorage.com sync --size-only --exclude "*.m3u8"  --exclude "*.key" --acl "public-read"  ' . $files_directory . '  s3://karbafubuket1',
        ];
        $command = implode(';', $commands);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->run(null, ['ENV_VAR_NAME' => 'value']);

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        //second upload privates
        $commands = [
            'sudo /root/s5cmd --endpoint-url=https://s3.ir-thr-at1.arvanstorage.com sync --size-only  --acl "private" ' . $files_directory . '  s3://karbafubuket1',
        ];
        $command = implode(';', $commands);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->run(null, ['ENV_VAR_NAME' => 'value']);
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        //third upload main video
        $commands = [
            "sudo /root/s5cmd --endpoint-url=https://s3.ir-thr-at1.arvanstorage.com sync --size-only " . $temp_video_url . '  s3://karbafubuket1',
        ];
        $command = implode(';', $commands);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->run(null, ['ENV_VAR_NAME' => 'value']);
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }


        return true;
    }

    public function removeFiles($user_id,$source_video_id)
    {
        $path = base_path() . '/public';
        $temp_folder = $path . '/temp_video/'. "temp_" . $user_id;
        Log::info($temp_folder);
        $files_directory = $path . "/" . $user_id;
        $commands = [
            'rm -f -r ' . $files_directory . '/',
            'rm -f -r ' . $temp_folder .'/',
        ];
        $command = implode(';', $commands);
        $process = Process::fromShellCommandline($command);

        $process->run(null, ['ENV_VAR_NAME' => 'value']);

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            return new ProcessFailedException($process);
        }
//        return $process->getOutput();
        return true;
    }
}
