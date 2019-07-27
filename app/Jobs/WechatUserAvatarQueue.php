<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WechatUserAvatarQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;
        $avatarPath = storage_path('app/avatars/wechat/'.$user->profile->openid.'.png');
        if($user->profile->headimgurl){
            $image = file_get_contents($user->profile->headimgurl);
            file_put_contents($avatarPath, $image);
        }else{
            \Log::error(__FILE__,[__FUNCTION__,__LINE__,$user->toArray()]);
        }
    }
}
