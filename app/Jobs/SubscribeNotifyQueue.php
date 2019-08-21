<?php

namespace App\Jobs;

use App\Http\Controllers\Api\LyLtsController;
use App\Http\Controllers\Api\LyMetaController;
use App\Models\Album;
use App\Models\AlbumSubscription;
use App\Models\LyAudio;
use App\Models\LyLts;
use App\Models\LyMeta;
use App\Models\Post;
use App\Models\User;
use App\Models\WechatAccount;
use App\Services\Wechat;
use App\Services\Wechat\MessageReplyHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SubscribeNotifyQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscription;
    protected $wechatAccount;
    protected $openId;
    protected $subscribeType;
    protected $subscribeId;
    protected $subscribeTypeCacheKey = 'last_subscribe_model_';
    protected $subscribeIdCacheKey = 'last_subscribe_id_';
    protected $commentTypeCacheKey = 'last_comment_model_';
    protected $commentIdCacheKey = 'last_comment_id_';
    protected $limitLink;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AlbumSubscription $subscription)
    {
        $wechatAccount = WechatAccount::find($subscription->wechat_account_id);
        $this->subscription = $subscription;
        $this->wechatAccount = $wechatAccount;
        // $user = User::with('profile')->find($subscription->user_id)->toArray();
        // $this->openId = $user['profile']['openid'];
        //用户名必须为 open ID
        $user = User::find($subscription->user_id);
        if (!$user) {
            Log::error(__FILE__, [__LINE__, $subscription->toArray(), 'subscription not found']);

            return;
        }
        $this->openId = $user->name;
        $this->subscribeType = $subscription->target_type;
        $this->subscribeId = $subscription->target_id;

        $this->subscribeTypeCacheKey .= $this->openId;
        $this->subscribeIdCacheKey .= $this->openId;
        $this->commentTypeCacheKey .= $this->openId;
        $this->commentIdCacheKey .= $this->openId;

        $this->limitLink = URL::route('focus');
        // $cache = Cache::tags('mp_limit');
        // $cacheKey = $this->openId;
        // $this->countLimit = $cache->get($cacheKey, 0);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subscribeCache = Cache::tags('subscribe');
        $commentCache = Cache::tags('comment');
        $subscription = $this->subscription;
        $res = false;
        $keyword = false;
        $commentCacheId = false;
        if ($this->subscribeType == LyMeta::class) {
            $lyMeta = LyMeta::find($subscription->target_id);
            $res = LyMetaController::get($lyMeta->code);
            $keyword = $lyMeta->index; //601
            if ($res && $res['type'] == 'music') {
                $commentCacheId = $res['comment_id'];
                $commentCacheType = LyMeta::class;
                $lastAudioId = LyAudio::where('target_type', $this->subscribeType)
                    ->where('target_id', $this->subscribeId)
                    ->orderBy('id', 'DESC')
                    ->pluck('slug')
                    ->first();
                $this->limitLink = URL::route('LyAudio.show', ['slug'=>$lastAudioId]);
            } else {
                Log::error(__FILE__, [__FUNCTION__, __LINE__, $res, $this->subscribeType]);

                return;
            }
        }

        if ($this->subscribeType == LyLts::class) {
            $lyLts = LyLts::find($subscription->target_id);
            $res = LyLtsController::get($lyLts->index);
            $keyword = '#'.$lyLts->index; //#601
            if ($res) {
                $commentCacheId = $res['comment_id'];
                $commentCacheType = LyLts::class;
            } else {
                Log::error(__FILE__, [__FUNCTION__, __LINE__, $this->subscribeType]);
            }
        }

        if ($this->subscribeType == Album::class) {
            $albumId = $this->subscribeId;
            $keyword = Album::MAX_INDEX + $albumId;
            $album = Album::find($albumId);
            //智慧养生 专辑和ly结合的情况
            if ($album->lymeta_id) {
                $commentCacheType = LyAudio::class;
                $posts = LyAudio::where('album_id', $albumId)->orderBy('play_at')->pluck('id')->all();
                $post = LyAudio::find($posts[$subscription->count - 1]);
                if (!$post) {
                    Log::error(__FILE__, [__FUNCTION__, __LINE__, $this->subscribeType, 'no post return']);

                    return;
                }
                $link = URL::route('LyAudio.show', ['slug'=>$post->slug]);
            } else {
                $commentCacheType = Post::class;
                $posts = Post::where('target_type', Album::class)->where('target_id', $albumId)->orderBy('order')->pluck('id')->all();
                $post = Post::find($posts[$subscription->count - 1]);
                if (!$post) {
                    Log::error(__FILE__, [__FUNCTION__, __LINE__, $this->subscribeType, 'no post return']);

                    return;
                }
                $link = URL::route('Post.show', ['slug'=>$post->slug]);
            }
            $total = count($posts);
            if ($subscription->count == $total) {
                // finished send
                $subscription->active = false;
                $subscription->save();
                Log::info(__FILE__, [__FUNCTION__, __LINE__, $this->subscribeType, 'finished']);

                return;
            }

            $res = $post->toWechat();
            $this->limitLink = URL::route('Post.show', ['slug'=>$post->slug]);
            if ($res) {
                //评论缓存
                $commentCacheId = $post->id;
            // $commentCacheType = Post::class;
            } else {
                Log::error(__FILE__, [__FUNCTION__, __LINE__, $this->subscribeType]);
            }
        }

        if ($res) {
            $this->finalReply($keyword, $res);
            //cache for comment
            $commentCache->put($this->commentTypeCacheKey, $commentCacheType, 720);
            $commentCache->put($this->commentIdCacheKey, $commentCacheId, 720);

            //记录已发送的次数
            $subscription->count += 1;
            $subscription->save();
        }
    }

    /**
     * @see MessageReplyHandler::finalReply()
     *
     * @param $keyword
     * @param $res
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function finalReply($keyword, $res)
    {
        $type = $res['type'];
        $wechatAccount = $this->wechatAccount;
        /* @var $app \EasyWeChat\officialAccount\Application */
        $app = Wechat::init($wechatAccount);
        $openId = $this->openId;
        // use Cache!!! 发送信息! // 用于提醒到期,20可以发10天
        // $table->tinyInteger('count_from_last')->unsigned()->default(0)->comment('mp_limit:count_from_last');

        $appCopyName = '公众号:'.$wechatAccount->name;
        //为资源添加【{$keyword}】标注
        if (in_array($type, ['news', 'music'])) {
            $res['content']['title'] = "【{$keyword}】".$res['content']['title'];
        }
        if ($type == 'music') {
            $subscription = $this->subscription;
            if (!isset($res['offset'])
                || $res['offset'] == 0
                || $subscription->target_type == LyLts::class
            ) {
                $res['content']['description'] .= ' 每日更新';
            } else {
                //不是每日更新的不发送
                return;
            }
            $mediaId = Wechat::THUMB_MEDIA_ID;
            $res['content']['thumb_media_id'] = $mediaId;
            $res['content']['description'] .= ' '.$appCopyName;
            if (isset($res['custom_message'])) {
                $res['custom_message'] = $res['content']['title']."\n".$res['custom_message'];
            } else {
                $res['custom_message'] = '';
            }

            $res['custom_message'] .= "💌直接回复您对本节目的【领受、笔记、感想】与其他听友分享\n或【节目建议】，小永将筛选发给本节目主持人，更有机会得到主持人的回应哦[Shhh][Twirl]";
            $res['custom_message'] .= "\n=========\n".Wechat::getRandomFaq();
        }
        $result = $app->customer_service
            ->message(Wechat::replyByType($type, $res['content']))
            ->to($openId)
            ->send();
        if ($result['errcode'] != 0) {
            //tpl 发送!
            $remark = '点击菜单[爱不止息]->[在线帮助]';
            //\n您也可以随时回复[退订88{$this->subscription->id}]以退订
            if ($this->limitLink != URL::route('focus')) {
                $remark = '👇点此查看今日推送';
            }
            $result = $app->template_message->send([
                'touser'      => $openId,
                'template_id' => 'BXQvCd7W_jE83WXR6nMNMXxoEM0Mgz0EUwqBGQ_ebKI',
                'url'         => $this->limitLink,
                'data'        => [
                    'first'    => '👉点击右下角菜单[爱不止息]->[一键续订],明天可继续接收',
                    'keyword1' => isset($res['content']['title']) ? $res['content']['title'] : '谢谢使用',
                    'keyword2' => '或回复【续订】,明日即可继续接收推送',
                    'remark'   => [$remark, '#173177'],
                ],
            ]);
            if ($result['errcode'] != 0) {
                Log::error(__FILE__, [__FUNCTION__, __LINE__, $result]);

                return false;
            }
        }

        {
            // region custom_message
            if (isset($res['custom_res']) && $res['custom_res']) {
                if ($res['custom_res']['type'] == 'news') {
                    $res['custom_res']['content']['title'] = "【{$keyword}】".$res['custom_res']['content']['title'];
                    if (isset($res['custom_messages'])) {
                        foreach ($res['custom_messages'] as $tmp) {
                            $res['custom_res']['content']['description'] .= "\n".$tmp;
                        }
                    }
                    $res['custom_res']['content']['description'] .= "💌直接回复您对本节目的【领受、笔记、感想】与其他听友分享,或【节目建议】，小永将筛选发给本节目主持人，更有机会得到主持人的回应哦\n微信中[浮窗]即可后台播放\n微信中右上角可以调整字号";
                    $res['custom_res']['content']['description'] .= "\n=========\n".Wechat::getRandomFaq();
                    $res['custom_res']['content']['url'] .= '?share='.$openId;
                }
                if ($res['custom_res']['type'] == 'music') {
                    $res['custom_res']['content']['title'] = "【{$keyword}】".$res['custom_res']['content']['title'];
                    $res['custom_res']['content']['thumb_media_id'] = Wechat::THUMB_MEDIA_ID;
                }
                $message = Wechat::replyByType($res['custom_res']['type'], $res['custom_res']['content']);
                $result = $app->customer_service->message($message)->to($openId)->send();
            } else {
                if (isset($res['custom_message'])) {
                    Wechat::customMessage($res, $app, $openId);
                }
            }
            // endregion custom_message
        }
        // region ga_push
        if (isset($res['ga_data'])) {
            Wechat::gaPush($res, $this->wechatAccount, $openId);
        }
        // endregion ga_push
    }
}
