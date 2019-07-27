<?php

namespace App\Console\Commands;

use App\Models\LyMeta;
use Illuminate\Console\Command;
use App\Jobs\LyAudioUpdateQueue;
use Illuminate\Support\Facades\Log;

class UpdateLyAudio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lyaudio:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每日更新摘要信息from txly2.net.';

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

        Log::info('CONSOLE', ['lyaudio:update Queue BEGIN']);
        foreach ($codes as $index => $code) {
            $target_id = $ids[$index];
            LyAudioUpdateQueue::dispatch($target_id, $code);
        }
        Log::info('CONSOLE', ['lyaudio:update Queue dispatched']);
    }
}
