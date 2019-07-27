<?php

namespace App\Jobs;

use App\Models\Album;
use App\Models\AlbumSubscription;
use App\Models\User;
use App\Models\WechatAccount;
use App\Services\Wechat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class WechatUserRecommendQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uid;
    protected $albumId;
    protected $recommendId; //被推荐的用户id

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uid, $albumId = false, $recommendId = false)
    {
        $this->uid = $uid; //推荐用户id
        $this->albumId = $albumId; //推荐专辑id
        $this->recommendId = $recommendId; //被推荐的用户id
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uid = $this->uid;
        $app = Wechat::init(1);
        //积分
        $message = '推荐成功';
        $user = User::find($uid);
        $link = URL::route('user.recommend');

        $albumId = $this->albumId;
        $recommendId = $this->recommendId;
        if ($albumId && $recommendId) {
            // 1.获取订阅id,2.获取Cache count,3.比较counts
            $subscribeType = Album::class;
            $subscription = AlbumSubscription::where('user_id', $uid)
                ->where('target_id', $albumId)
                ->where('target_type', $subscribeType)
                ->first();
            if ($subscription) {
                // $cache = Cache::tags('recommendSubscription');
                // $cacheKey = 'subscription_'.$subscription->id;
                // $recommentCounts = $cache->get($cacheKey);

                //第一个推荐人,不加,号 ==$recommentCounts ++;
                if ($subscription->recommenders) {
                    $subscription->recommenders .= ','.$recommendId;
                } else {
                    $subscription->recommenders = $recommendId;
                }
                $recommentCounts = count(array_unique(array_filter(explode(',', $subscription->recommenders))));
                // $cache->put($cacheKey, $recommentCounts, 720);

                $content = '啊哦,出错了啊,请告诉小永微信: yongbuzhixi_love';
                $minFreeCount = AlbumSubscription::FREE_SHTARE_MIN; //3

                if ($recommentCounts == $minFreeCount) {
                    // && $subscription->price<0
                    $subscription->count = 1; //发第二个!
                    $subscription->price = $recommentCounts * 0.01; //价格改为正
                    $subscription->save();
                    //推荐成功,发送第二个图文!更新$subscription->counts = 2
                    SubscribeNotifyQueue::dispatch($subscription);
                    $content = "恭喜您, 好友助力成功!\n我们将每天一集发送本课程给您。\n<a href='{$link}'>感谢助力好友</a>";
                }
                //超过3人
                if ($recommentCounts > $minFreeCount) {
                    $subscription->price = $recommentCounts * 0.01; //价格改为推荐人数
                    $subscription->save();
                    $content = "恭喜您, 又有一个好友为您助力成功[胜利]\n<a href='{$link}'>点击查看</a>";
                }
                //小于3人
                if ($recommentCounts < $minFreeCount) {
                    $left = $minFreeCount - $recommentCounts;
                    $content = "恭喜,一位好友助力成功!\n还差{$left}个好友💪\n<a href='{$link}'>感谢助力好友</a>";
                    $subscription->save();
                }
                // $wechatAccount = WechatAccount::find($subscription->wechat_account_id);
                // $app = Wechat::init($wechatAccount);
                $app = Wechat::init();
                $openId = $user->name;
                $type = 'text';
                $res = [
                    'custom_message' => $content,
                ];
                Wechat::customMessage($res, $app, $openId);
            }

            return;
        }

        $user->addPoints(User::POINT_PRE_USER_RECOMMEND, $message);
        //通知
        // $link = URL::route('user.recommend');
        $totalPoints = $user->currentPoints();
        $nickname = $user->profile->nickname;
        $app->template_message->send([
            'touser'      => $user->profile->openid,
            'template_id' => 'XpRmCnx6kFbaFW2euenvH3uhcCol2aJrTLnTktMReyM',
            'url'         => $link,
            'data'        => [
                'first'    => '推荐成功, 您的积分账户变更如下',
                'keyword1' => $nickname,
                'keyword2' => '获得'.User::POINT_PRE_USER_RECOMMEND.'积分',
                'keyword3' => $totalPoints,
                'remark'   => ['感谢您的使用，永不止息，感恩有你！', '#173177'],
            ],
        ]);
    }
}
