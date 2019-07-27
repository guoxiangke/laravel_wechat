<?php

namespace App\Console\Commands;

use App\Jobs\SubscribeNotifyQueue;
use App\Models\AlbumSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubscribeNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscribe:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send wechat custom message of subscription.';

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
        AlbumSubscription::where('active',true)
            ->where('price', '>=', 0)
            ->chunk(100, function ($subscriptions) {
            foreach ($subscriptions as $subscription){
                $sendAtHour = $subscription->send_at;
                $nowHour = date('H');
                if($nowHour == $sendAtHour){
                    //取消关注的不再发送
                    if($subscription->user && !$subscription->user->subscribe){
                        $subscription->delete();
                        Log::info(__FILE__, ['unsubscribed subscription, delete it!', $subscription->id,$subscription->user->profile->nickname]);
                        continue;
                    }
                    if($subscription->rrule){
                        // todo
                    }else{
                        SubscribeNotifyQueue::dispatch($subscription);
                    }
                }
            }
        });
    }
}
