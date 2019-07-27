<?php

namespace App\Console\Commands;

use App\Jobs\LyAudioDownloadQueue;
use App\Models\LyMeta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LyAudioDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lyaudio:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动下载from txly2.net to /tmp/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $codes = LyMeta::whereNull('stop_play_at')->select('code', 'id')->get();
        $ids = array_pluck($codes, 'id');
        $codes = array_pluck($codes, 'code');

        Log::info('CONSOLE', ['lyaudio:download Queue BEGIN']);
        foreach ($codes as $index => $code) {
            LyAudioDownloadQueue::dispatch($code);
        }
        Log::info('CONSOLE', ['lyaudio:download Queue dispatched']);
    }
}
