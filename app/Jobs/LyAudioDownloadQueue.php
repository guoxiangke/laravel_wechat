<?php

//todo delet!

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LyAudioDownloadQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $code;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $code = $this->code;

        $dateStr = date('ymd'); //161129
        $mp3file = $code.$dateStr.'.mp3'; //ee181016.mp3
        $year = date('Y');
        //http://txly2.net/ly/audio/2018/ee/ee181016.mp3
        $url = "https://txly2.net/ly/audio/$year/$code/$mp3file";
        $headers = @get_headers($url)
            or die("Unable to connect to $url");
        if ($headers[0] != 'HTTP/1.1 200 OK') {
            return;
        }
        Log::info('LyAudioDownloadQueue', ['Curl:download begin '.$url]);
        $downloadPath = "/tmp/$mp3file";

        // open file descriptor
        $fp = fopen($downloadPath, 'w+') or die('Unable to write a file');
        // file to download
        $ch = curl_init($url);
        // enable SSL if needed
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // output to file descriptor
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // set large timeout to allow curl to run for a longer time
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_USERAGENT, 'any');
        // Enable debug output
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_exec($ch);
        //If there was an error, throw an Exception
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }

        //Get the HTTP status code.
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //Close the cURL handler.
        curl_close($ch);

        if ($statusCode == 200) {
            Log::info('LyAudioDownloadQueue', ['Curl:Downloaded '.$mp3file]);
        } else {
            Log::error('LyAudioDownloadQueue', ['Curl Code '.$statusCode]);
        }
        fclose($fp);
        Log::info('CONSOLE', ['Curl:download end '.$mp3file]);
        //php exec command
        $dest = "/share/lytx/$year/$code/";
        exec("echo '/usr/local/bin/oneupload $downloadPath $dest && sudo rm $downloadPath' >> /tmp/upload.$dateStr.sh");
    }
}
