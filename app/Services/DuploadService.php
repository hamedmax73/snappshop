<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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

    public function saveInDisk($video_id, $links)
    {
        $path = base_path() . '/public';
        var_dump($path);
        $this->setVideoId($video_id);
        $video_id = $this->getVideoId();
        $URLs = $this->linkText($links);
        $commands = [
            'cd ' . $path,
            'mkdir ' . $video_id,
            'cd ' . $video_id,
            'rm -f list.txt',
            'touch list.txt',
            'echo "' . $URLs . '" >> list.txt'
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

    public function downloadFiles()
    {
        $path = base_path() . '/public';
        var_dump($path);
        $video_id = $this->getVideoId();
        $commands = [
            'cd ' . $path,
            'cd ' . $video_id,
            'xargs -n 1 -P 0 curl -s -O < list.txt'
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

    public function syncFiles($video_id)
    {
        $path = base_path() . '/public';
        $files_directory = $path . "/" . $video_id;
        var_dump($files_directory);
        $commands = [
            'echo h1475h | sudo -S /root/s5cmd --endpoint-url=https://s3.ir-thr-at1.arvanstorage.com sync --size-only ' . $files_directory . '  s3://karbafubuket1'
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

    public function removeFiles($video_id)
    {
        $path = base_path() . '/public';
        $files_directory = $path . "/" . $video_id;
        $commands = [
            'rm -f -r ' . $files_directory .'/',
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
