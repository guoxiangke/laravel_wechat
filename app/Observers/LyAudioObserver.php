<?php

namespace App\Observers;

use App\Models\LyAudio;
use Hashids\Hashids;
use Illuminate\Support\Facades\Config;

class LyAudioObserver
{
    /**
     * Handle the post "created" event.
     *
     * @param \App\Post $post
     *
     * @return void
     */
    public function created(LyAudio $post)
    {
        if (!$post->slug) {
            $hashids = new Hashids(Config::get('app.name'), 11);
            $post->slug = $hashids->encode($post->id, time());
            $post->save();
        }
    }
}
