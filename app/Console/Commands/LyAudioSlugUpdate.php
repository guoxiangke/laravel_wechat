<?php

namespace App\Console\Commands;

use Hashids\Hashids;
use App\Models\LyAudio;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Config;

class LyAudioSlugUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lyaudio:slug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'udpate all slugs';

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
        LyAudio::all()->map(function ($post) {
            $hashids = new Hashids(Config::get('app.name'), 11);
            $post->slug = $hashids->encode($post->id, time());
            $post->save();
        });
    }
}
