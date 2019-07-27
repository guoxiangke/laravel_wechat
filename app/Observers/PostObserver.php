<?php

namespace App\Observers;

use App\Models\Post;
use Hashids\Hashids;
use Illuminate\Support\Facades\Config;

class PostObserver
{
    /**
     * Handle the post "created" event.
     *
     * @param  \App\Post  $post
     * @return void
     */
    public function created(Post $post)
    {
        if(!$post->slug){
          $hashids = new Hashids(Config::get('app.name'), 11);
          $post->slug = $hashids->encode($post->id, time());
          $post->save();
        }
    }
}
