<?php

namespace App\Observers;

use App\Jobs\SubscribeNotifyQueue;
use App\Models\AlbumSubscription;
use App\Models\User;
use App\Models\WechatPayOrder;
use App\Services\Wechat;

class WechatPayOrderObserver
{
    /**
     * Handle the wechat pay order "created" event.
     *
     * @param \App\WechatPayOrder $wechatPayOrder
     *
     * @return void
     */
    public function created(WechatPayOrder $wechatPayOrder)
    {
        //
    }

    /**
     * Handle the wechat pay order "updated" event.
     * 支付成功后,更新订阅价格为正,即表示支付成功!
     *
     * @param \App\WechatPayOrder $wechatPayOrder
     *
     * @return void
     */
    public function updated(WechatPayOrder $wechatPayOrder)
    {
        if ($wechatPayOrder->success) {
            if ($wechatPayOrder->target_type && $wechatPayOrder->target_id) {
                $subscription = AlbumSubscription::where('user_id', $wechatPayOrder->user_id)
                ->where('target_type', $wechatPayOrder->target_type)
                ->where('target_id', $wechatPayOrder->target_id)
                ->first();
                if ($subscription && $subscription->price < 0) {
                    $subscription->price = -$subscription->price;
                    $subscription->count = 1;
                    $subscription->save();
                    //支付成功,发送第二个图文!更新$subscription->counts = 2
                    SubscribeNotifyQueue::dispatch($subscription);
                    //支付成功提醒!
                    $app = Wechat::init(1);
                    $user = User::find($wechatPayOrder->user_id);
                    $openId = $user->name;
                    $res['type'] = 'text';
                    $res['content'] = "/:strong支付成功!\n马上发送第2集\n第3集明天发送";
                    $reply = Wechat::custom($res, $app, $openId);
                }
            }
        }
    }

    /**
     * Handle the wechat pay order "deleted" event.
     *
     * @param \App\WechatPayOrder $wechatPayOrder
     *
     * @return void
     */
    public function deleted(WechatPayOrder $wechatPayOrder)
    {
        //
    }

    /**
     * Handle the wechat pay order "restored" event.
     *
     * @param \App\WechatPayOrder $wechatPayOrder
     *
     * @return void
     */
    public function restored(WechatPayOrder $wechatPayOrder)
    {
        //
    }

    /**
     * Handle the wechat pay order "force deleted" event.
     *
     * @param \App\WechatPayOrder $wechatPayOrder
     *
     * @return void
     */
    public function forceDeleted(WechatPayOrder $wechatPayOrder)
    {
        //
    }
}
