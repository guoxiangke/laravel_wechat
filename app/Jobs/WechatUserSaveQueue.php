<?php

//todo delete!!!!

namespace App\Jobs;

use App\Models\User;
use App\Services\WechatUserProfileHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WechatUserSaveQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $openId;
    protected $recommendId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($openId, $recommendId = 0)
    {
        $this->openId = $openId;
        $this->recommendId = $recommendId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::newUser($this->openId, User::DEFAULT_ROLE, $this->recommendId);
        //update UserProfile
        WechatUserProfileHelper::updateProfile($user);
    }
}
