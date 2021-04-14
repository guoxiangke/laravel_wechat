<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/2
 * Time: 下午2:22.
 */

namespace App\Services\Wechat;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use ReflectionClass;
use App\Models\Album;
use App\Models\LyLts;
use App\Models\LyMeta;
use App\Models\LyAudio;
use App\Services\Upyun;
use App\Services\Wechat;
use App\Models\WechatAccount;
use App\Models\WechatPayOrder;
use App\Jobs\WechatPosterQueue;
use App\Models\WechatAutoReply;
use App\Jobs\WechatLinkSaveQueue;
use App\Models\AlbumSubscription;
use App\Jobs\WechatUserAvatarQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Jobs\WechatUserProfileQueue;
use Illuminate\Support\Facades\Cache;
use App\Jobs\WechatUserRecommendQueue;
use Illuminate\Support\Facades\Config;
use EasyWeChat\Kernel\Messages\Transfer;
use App\Services\Wechat\Resources\LyHandle;
use App\Services\Wechat\Resources\LtsHandle;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

class MessageReplyHandler implements EventHandlerInterface
{
    protected $msgType;
    protected $toUserName;
    protected $keyword;
    protected $appCopyName;
    protected $subscribeCache;
    protected $commentCache;
    protected $subscribeTypeCacheKey = 'last_subscribe_model_';
    protected $subscribeIdCacheKey = 'last_subscribe_id_';
    protected $commentTypeCacheKey = 'last_comment_model_';
    protected $commentIdCacheKey = 'last_comment_id_';
    // 每收听一个节目. 奖励10积分(每天最高可积100分).
    // 每发布一条内容, 奖励50积分(每日最高可积1000分).
    protected $pointsCache;
    protected $pointsCommentCacheKey = 'day_limit_comment_';
    protected $pointsMusicCacheKey = 'day_limit_music_';
    protected $isLyApp = false;
    protected $isMainApp = false;
    protected $isCertified = false;
    protected $openId = false;
    protected $wechatAccount = false;
    protected $app = false;
    protected $user = false;
    protected $userIsNew = false;

    public function handle($message = null)
    {
        $reply = null;
        $this->msgType = $message['MsgType'];
        $this->toUserName = $message['ToUserName'];

        $wechatAccount = WechatAccount::where('to_user_name', $this->toUserName)
            ->firstOrFail();
        $this->wechatAccount = $wechatAccount;
        /* @var $app \EasyWeChat\officialAccount\Application */
        $this->app = Wechat::init($wechatAccount);
        $this->appCopyName = '公众号:'.$wechatAccount->name;
        $this->isCertified = $wechatAccount->is_certified ? true : false;
        if ($wechatAccount->name == '良朋益友') {
            $this->isLyApp = true;
        }
        if ($this->toUserName == Wechat::MAIN_APP) {
            $this->isMainApp = true;
        }
        $this->openId = $message['FromUserName'];

        if ($this->isCertified) {
            $this->subscribeTypeCacheKey .= $this->openId;
            $this->subscribeIdCacheKey .= $this->openId;
            $this->commentTypeCacheKey .= $this->openId;
            $this->commentIdCacheKey .= $this->openId;
            $this->subscribeCache = Cache::tags('subscribe');
            $this->commentCache = Cache::tags('comment');
            $user = User::with('profile')->where('name', $this->openId)->first();
            $this->user = $user;
            if ($this->isMainApp) {
                if (! $user) {
                    //如果是扫描关注,在event环节创建新用户
                    $this->user = User::newUser($this->openId);
                    $this->userIsNew = true;
                    WechatUserProfileQueue::dispatch($this->user)->delay(now()->addSeconds(5));
                } else {
                    if (is_null($user->profile) || ! isset($user->profile->headimgurl)) {
                        WechatUserProfileQueue::dispatch($user)->delay(now()->addSeconds(5));
                    }
                    $avatarPath = storage_path('app/avatars/wechat/'.$this->openId.'.png');
                    if (! file_exists($avatarPath) && isset($user->profile->headimgurl)) {
                        WechatUserAvatarQueue::dispatch($user)->delay(now()->addSeconds(10));
                    }
                }
                $this->pointsCache = Cache::tags('points');
                $this->pointsCommentCacheKey .= $this->openId.'_'.date('ymd');
                $this->pointsMusicCacheKey .= $this->openId.'_'.date('ymd');
            }
        }

        $handle = 'handle_'.$message['MsgType'];
        //   https://jmfeurprier.com/2010/01/03/method_exists-vs-is_callable/
        //  method_exists(__CLASS__, $handle)
        if (is_callable([__CLASS__, $handle])) {
            $reply = $this->$handle($message);
        }else{
            $keyword = 'unknown_type';
            $res = $this->autoReply($this->toUserName, $keyword);
            $reply = $this->finalReply($keyword, $res);
        }

        return $reply;
    }

    public function handle_voice($message)
    {
        $keyword = 'voice_received';
        $res = $this->autoReply($this->toUserName, $keyword);

        return $this->finalReply($keyword, $res);
    }

    /**
     * @param $message
     *
     * @return Music|array|mixed|null|string
     */
    public function handle_text($message)
    {
        $toUserName = $this->toUserName;
        $keyword = strip_tags(trim($message['Content']));
        $keyword = semiangle_texts($keyword);
        $search = ['[', ']', "'", '"', '收听', '○', 'o', '〇', '|'];
        $replace = ['', '', '', '', '', '0', '0', '0', ''];
        $this->keyword = str_replace($search, $replace, $keyword);

        $res = null;
        $wechatAccount = $this->wechatAccount;
        $resourcesEnabled = $wechatAccount->resources;
        // 评论/订阅系统是否开启，
        $subscribeEnabled = isset($resourcesEnabled['subscribe']) ? $resourcesEnabled['subscribe'] : false;
        $commentEnabled = isset($resourcesEnabled['comment']) ? $resourcesEnabled['comment'] : false; //todo
        $commentEnabled = true;
        $subscribeType = false;
        $commentType = false;

        // 判断是否开启资源
        $lyEnabled = isset($resourcesEnabled['lymeta']) ? $resourcesEnabled['lymeta'] : false;
        $ltsEnabled = isset($resourcesEnabled['lylts']) ? $resourcesEnabled['lylts'] : false;
        //region ly & lts
        if (! $res && ($lyEnabled || $ltsEnabled) && preg_match('/\d{3,}/', $keyword)) {
            // region 以#开头的 #101XXX-#909XXX
            if ($keyword[0] == '#') {
                // $keyword = str_replace('#', '', $keyword);
                $intKeyword = (int) filter_var($keyword, FILTER_SANITIZE_NUMBER_INT);
                $firstIndex = substr($intKeyword, 0, 3);
                if ($firstIndex >= 100 && $firstIndex < 1000) {
                    $res = LtsHandle::process($intKeyword);
                }
                //cache last subscribe type
                if ($res && $res['type'] == 'music') {
                    //$res['subscribe_id'] = $firstIndex;
                    $subscribeType = LyLts::class;
                    $commentType = LyLts::class;
                }
            }
            // endregion lts

            // region lymeta
            if (! $res) {
                //todo 汉字关键词：旷野吗哪 语音识别 昨天的，今天的
                $res = LyHandle::process($keyword, $this->isLyApp);
                //cache last subscribe type
                if ($res) {
                    $subscribeType = LyMeta::class;
                    $commentType = LyMeta::class;
                }
            }
            // endregion lymeta
            // 只有【彩云助手】可以订阅和评论！
            if ($res && $this->isMainApp) {
                if ($subscribeEnabled && $subscribeType && isset($res['subscribe_id'])) {
                    $this->subscribeCache->put($this->subscribeTypeCacheKey, $subscribeType, 720);
                    $this->subscribeCache->put($this->subscribeIdCacheKey, $res['subscribe_id'], 720);

                    $targetId = explode(',', $res['subscribe_id'])[0];
                    $isSubscribe = AlbumSubscription::where('user_id', $this->user->id)
                        ->where('active', 1)
                        ->where('target_type', $subscribeType)
                        ->where('target_id', $targetId)
                        ->where('wechat_account_id', $this->wechatAccount['id'])
                        ->first();
                    //每个用户只能订阅1个免费的LY
                    //订阅会员可以有3个! todo
                    if (! $isSubscribe && $this->user->get_free_subscription_counts() < AlbumSubscription::FREE_COUNT_LIMIT) {
                        $res['custom_messages'][] = '🎉回复【订阅】即可订阅本节目哦';
                        $domain = Upyun::DOMAIN.'/videos/2019/faq';
                        $res['custom_messages'][] = "<a href='{$domain}/如何订阅和退订.mp4'>====>订阅帮助</a>";
                    } else {
                        if ($keyword >= 641 && $keyword <= 645) {
                            $res['custom_messages'][] = "[抱拳]本节目不可评论!\n请回复【500】订阅良院课程获取节目后再评论";
                        } else {
                            $res['custom_messages'][] = "💌直接回复您的[领受、笔记、感想、节目建议]与其他听友分享\n小永将筛选发给本节目主持人，更有机会得到主持人的回应哦[Shhh][Twirl]";
                        }
                    }
                }

                // region 加积分 //600不加分,600没有title
                if (isset($res['content']['title'])) {
                    // 每收听一个节目. 奖励10积分(每天最高可积100分).
                    $this->pointsCache = Cache::tags('points');
                    // $this->pointsCache->put($this->pointsCommentCacheKey)
                    $points = $this->pointsCache->get($this->pointsMusicCacheKey);
                    if ($points < User::POINT_MUSIC_DAY_LIMIT) {
                        $this->pointsCache->put($this->pointsMusicCacheKey, $points + 10, 24 * 60);
                        $amount = 10; // (Double) Can be a negative value
                        $message = "获取【{$keyword}】".$res['content']['title'];
                        $this->user->addPoints($amount, $message);
                        $res['custom_messages'][] = '恭喜您获得'.User::POINT_PRE_MUSIC.'积分!';
                    } else {
                        $res['custom_messages'][] = '恭喜您,今日得满分!';
                    }
                    $res['custom_messages'][] = '现在总积分:'.$this->user->currentPoints().'积分!';
                }
                // endregion
            }
        }
        //endregion

        // region P1~P123 post article!
        if (! $res && $this->keyword[0] == 'p' && preg_match('/\d{1,}/', $this->keyword)) {
            preg_match('/(\d{1,})/', $this->keyword, $matches);
            if ($matches && isset($matches[1])) {
                //todo 对不起,这是付费专辑的内容! or only for admin
                $post = Post::find($matches[1]);
                if ($post) {
                    $res = $post->toWechat();
                    $commentType = Post::class;
                }
            }
        }
        // endregion

        // region L1~L123 lyaudio article!
        if (! $res && $this->keyword[0] == 'l' && preg_match('/\d{1,}/', $this->keyword)) {
            preg_match('/(\d{1,})/', $this->keyword, $matches);
            if ($matches && isset($matches[1])) {
                //todo 对不起,这是付费专辑的内容! or only for admin
                $post = LyAudio::find($matches[1]);
                if ($post) {
                    $res = $post->toWechat();
                    $commentType = LyAudio::class;
                }
            }
        }
        // endregion

        //region 7XX 订阅 + 退订88
        if (! $res && $this->isMainApp) {
            $res = $this->_subscribe();
            if (isset($res['comment_type'])) {
                //Post::class; or LyAudio::class;
                $commentType = $res['comment_type'];
            } else {
                \Log::error('NO_$commentType', [$res]);
            }
            // 推荐二维码!
            if (! $res) {
                if ($this->keyword == '活动' || $this->keyword == '推荐') {
                    $res = $this->_recommend();
                }
            }
            if (! $res) {
                if (starts_with($keyword, 'http') && strpos($keyword, 'mp.weixin.qq.com/') !== false) {
                    // 1.check user permisson
                    WechatLinkSaveQueue::dispatch($keyword, $this->user->id)->delay(now()->addSeconds(5));
                    //todo 点此查看您收集的文章,您总共收集了XX篇文章
                    return '已加入收集队列,永不止息,感恩有你[抱拳]';
                }
                //评论系统 只有彩云app可以评论！至少8个字
                if (strlen($keyword) > 24) {
                    $res = $this->_comment();
                }
            }
        }
        //endregion

        // region ly文字识别
        if (! $res && $lyEnabled) {
            $lyMeta = LyMeta::active()->where('name', $this->keyword)->first();
            if ($lyMeta) {
                //todo 汉字关键词：旷野吗哪 语音识别 昨天的，今天的
                if ($this->isLyApp) {
                    $keyword = $lyMeta->ly_index;
                } else {
                    $keyword = $lyMeta->index;
                }
                $res = LyHandle::process($keyword, $this->isLyApp);
                //cache last subscribe type
                if ($res) {
                    $subscribeType = LyMeta::class;
                    $commentType = LyMeta::class;
                }
            }
        }
        // endregion


        //region for simai //77001 771 77002 77583 77999
        if (! $res
            && $wechatAccount->name == '思麦团契'
            && preg_match('/77(\d{1,})/', $keyword, $matches)) {
            if ($matches && isset($matches[1])) {
                $offset = (int) $matches[1];

                $cache = Cache::tags('lyaudio');
                $cacheKey = 'simai77';
                $reversed = $cache->get($cacheKey);
                if (! $reversed) {
                    //todo cache all str!!
                    $url = 'https://raw.githubusercontent.com/simai2019/vuepress/master/docs/audio/list.md';
                    $str = file_get_contents($url);
                    preg_match_all('/\- \[【\d+】(.+)/', $str, $matches2);
                    $reversed = array_reverse($matches2[1]);
                    $now = Carbon::now();
                    $ttl = $now->diffInMinutes($now->copy()->endOfDay());
                    $cache->add($cacheKey, $reversed, now()->addMinutes($ttl));
                }
                //770
                if ($offset == 0) {
                    $url = 'https://raw.githubusercontent.com/simai2019/vuepress/master/docs/audio/list.md';
                    $str = file_get_contents($url);
                    preg_match_all('/\- \[(【\d+】.+)\]/', $str, $matches2);
                    $content = '';
                    $count = 0;
                    foreach ($matches2[1] as $value) {
                        if ($count >= 10) {
                            break;
                        }
                        $content .= $value.PHP_EOL;
                        $count++;
                    }
                    $res = [
                        'type'          => 'text',
                        'content' => $content,
                        'ga_data'       => [
                            'category' => '770',
                            'action'   => 'list_menu',
                        ],
                    ];
                } else {
                    $match = $reversed[$offset - 1];
                    $match = explode('](', $match);
                    $title = trim($match[0]);
                    $hqUrl = str_replace(')', '', $match[1]);
                    $hqUrl = 'https://file.simai.life'.$hqUrl;

                    $default_desc = '点击▶️收听';
                    $res = [
                        'type'          => 'music',
                        'ga_data'       => [
                            'category' => '77',
                            'action'   => $title,
                        ],
                        'offset'   => $offset,
                        'content'  => [
                            'title'          => $title,
                            'description'    => $default_desc,
                            'url'            => $hqUrl,
                            'hq_url'         => $hqUrl,
                            'thumb_media_id' => null,
                        ],
                    ];
                }
            }
        }
        //endregion

        //region for 66
        if (! $res
            // && $wechatAccount->name == '思麦团契'
            && preg_match('/88(\d{1,})/', $keyword, $matches)) {
            if ($matches && isset($matches[1])) {
                $offset = (int) $matches[1];
                $cache = Cache::tags('lyaudio');
                $cacheKey = 'fm88';
                $reversed = $cache->get($cacheKey);
                if (! $reversed) {
                    //todo cache all str!!
                    $url = 'https://raw.githubusercontent.com/flychat/vuepress/master/docs/Life/Praise.md';
                    $str = file_get_contents($url);
                    preg_match_all('/\- (.+)/', $str, $matches2);
                    $reversed = array_reverse($matches2[1]);
                    $now = Carbon::now();
                    $ttl = $now->diffInMinutes($now->copy()->endOfDay());
                    $cache->add($cacheKey, $reversed, now()->addMinutes($ttl));
                }
                $match = $reversed[$offset - 1];
                $match = explode('|', $match);
                $title = trim($match[2]);
                $hqUrl = 'http://file.simai.life/other/playlist/'.trim($match[1]).'.mp3';
                $mp4 = 'http://file.simai.life/other/playlist/'.trim($match[1]).'.mp4';

                $default_desc = '点击▶️收听';
                $res = [
                    'type'          => 'music',
                    'ga_data'       => [
                        'category' => '88',
                        'action'   => $title,
                    ],
                    'offset'   => $offset,
                    'custom_message' => $mp4,
                    'content'  => [
                        'title'          => $title,
                        'description'    => $default_desc,
                        'url'            => $hqUrl,
                        'hq_url'         => $hqUrl,
                        'thumb_media_id' => null,
                    ],
                ];
            }
        }
        //endregion

        // region
        if (! $res) {
            // 自动回复 for specific account
            $res = $this->autoReply($toUserName, $keyword);
            // 自动回复 for All account
            if (! $res) {
                $res = $this->autoReply(WechatAutoReply::ALL_ACCOUNTS, $keyword);
            }
        }
        // endregion

        if ($res) {
            if ($this->isCertified && $commentEnabled && $commentType && isset($res['comment_id'])) {
                $this->commentCache->put($this->commentTypeCacheKey, $commentType, 720);
                $this->commentCache->put($this->commentIdCacheKey, $res['comment_id'], 720); //*,*
            }

            return $this->finalReply($keyword, $res);
        }
    }

    //良友or良院最后处理
    public function finalReply($keyword, $res)
    {
        $app = $this->app;
        $openId = $this->openId;
        if (! isset($res['type'])) {
            //debug todo delete!!!
            Log::error(__CLASS__, [__FUNCTION__, __LINE__, $keyword, $res]);
        }
        $type = $res['type'];
        $appCopyName = $this->appCopyName;
        if ($type == 'music') {
            $res['content']['title'] = "【{$keyword}】".$res['content']['title'];
            $res['content']['description'] .= ' '.$appCopyName;
            if (! isset($res['offset']) || $res['offset'] == 0) {
                $res['content']['description'] .= ' 每日更新';
            }
            if (isset($res['custom_message'])) {
                $res['custom_message'] = $res['content']['title']."\n".$res['custom_message'];
            }
            //todo + 回复D，订阅本节目每日内容，定时⏰提醒！
            //记录last reply 订阅专辑id放入cache！
        }
        if ($type == 'news') {
            $res['content']['title'] = "【{$keyword}】".$res['content']['title'];
        }
        $content = $res['content'];
        $reply = Wechat::replyByType($type, $content);

        // region custom_message
        if (Config::get('app.env') != 'development' && $this->isCertified) {
            if (isset($res['custom_res']) && $res['custom_res']) {
                $customRes = $res['custom_res'];
                $customRes['content']['title'] = "【{$keyword}】".$customRes['content']['title'];
                if ($customRes['type'] == 'news') {
                    // if(isset($res['custom_message'])){
                    //     $customRes['content']['title'] = $res['content']['title'] ;
                    // }
                    if (isset($res['custom_messages'])) {
                        $tmps = '';
                        foreach ($res['custom_messages'] as $tmp) {
                            $tmps .= $tmp."\n";
                        }
                        $tmps .= "\n=========\n".Wechat::getRandomFaq();
                        $customRes['content']['description'] = $tmps.$customRes['content']['description']."\n"."微信中[浮窗]即可后台播放\n微信中右上角可以调整字号";
                    }
                    $customRes['content']['url'] .= '?share='.$openId;
                }

                if ($customRes['type'] == 'music') {
                    $customRes['content']['thumb_media_id'] = Wechat::THUMB_MEDIA_ID;
                    if (isset($res['custom_message'])) {
                        $message = Wechat::replyByType('text', $res['custom_message']);
                        $app->customer_service->message($message)->to($openId)->send();
                    }
                }
                Wechat::custom($customRes, $app, $openId);
            } else {
                if (isset($res['custom_message']) || isset($res['custom_messages'])) {
                    Wechat::customMessage($res, $app, $openId);
                }
            }
        }
        // endregion custom_message

        // region ga_push
        if (isset($res['ga_data'])) {
            Wechat::gaPush($res, $this->wechatAccount, $this->toUserName);
        }
        // endregion ga_push

        return $reply;
    }

    /**
     * @desc 自动回复 by 规则
     *
     * @param $toUserName
     * @param string $keyword
     *
     * @return bool|Transfer
     */
    public function autoReply($toUserName, $keyword = '')
    {
        $autoReplies = WechatAutoReply::where('to_user_name', $toUserName)
            ->orWhere('to_user_name', 'gh_all');
        if ($keyword == 'subscribe') {
            $autoReplies->where('patten', 'subscribe');
        } else {
            $autoReplies->where('patten', '!=', 'subscribe');
        }
        $autoReplies = $autoReplies->orderby('weight', 'desc')
            ->get()
            ->toArray();
        if ($autoReplies) {
            foreach ($autoReplies as $autoReply) {
                $pattens = $autoReply['patten'];
                $type = strtolower($autoReply['type']);
                $content = $autoReply['content'];
                $pattens = explode(PHP_EOL, $pattens); // PHP_EOF 多行 $patten
                foreach ($pattens as $patten) {
                    //todo
                    // 'subscribe' || $patten == 'resubscribe'  || $patten == 'transfer'
                    if ($patten == $keyword
                        || preg_match('/'.$patten.'/', $keyword)
                    ) {
                        if ($type == 'news') {
                            $content = json_decode($content, 1)[0];
                        }
                        $res = [
                            'type'    => $type,
                            'content' => $content,
                            'ga_data' => [
                                'category' => 'autoReply',
                                'action'   => 'autoReply',
                            ],
                        ];

                        return $res;
                    }
                }
            }
        }

        return false;
    }

    //todo
    public function handle_event($message)
    {
        $res = null;
        $gaAction = $message['FromUserName'];
        $user = $this->user;

        $event = $message['Event'];
        $keyword = $event;
        $content = false;

        if (isset($message['Event']) && $message['Event'] == 'CLICK') {
            $message['Content'] = $message['EventKey'];

            return $this->handle_text($message);
        }

        //推荐专辑扫码数据处理
        $albumId = false;
        $recommenderId = false;
        if (isset($message['EventKey'])) {
            $eventKeys = str_replace('sharefrom_', '', $message['EventKey']);
            $eventKeys = str_replace('qrscene_', '', $eventKeys);
            $eventKeys = explode('_', $eventKeys);
            $recommenderId = $eventKeys[0];
            if (isset($eventKeys[1])) {
                $albumId = $eventKeys[1];
            }
        }
        if ($albumId) {
            $album = Album::findOrFail($albumId);
            $albumIndex = $album->getIndex();
        }

        if ($event == 'SCAN') {
            //"Event":"SCAN","EventKey":"sharefrom_9"
            $keyword = 'scan_sharefrom';
            //重新发送专辑订阅 第一个图文
            if ($albumId) {
                $content = "您已关注, 转发他的二维码海报, 呼唤新朋友帮他助力吧!\n回复【{$albumIndex}】和朋友组团一起挑战吧!\n回复不带【中括号】";
            } else {
                $content = "[鼓掌]您已关注, 欢迎回来\n[抱拳]回复【600】获取节目菜单\n回复不带【中括号】";
            }
            if ($recommenderId == $user->id) {
                $link = URL::route('user.recommend');
                $content = "自己扫码无效\n<a href='{$link}'>点此查看推荐好友</a>";
                //test
                // WechatUserRecommendQueue::dispatch($recommenderId,$albumId,$user->id)->delay(now()->addSeconds(3));
            }
        }

        if ($event == 'subscribe') {
            if (isset($message['EventKey']) && ! is_null($message['EventKey'])) {
                // 扫推荐码关注
                //"Event":"subscribe","EventKey":"qrscene_sharefrom_9"
                //sharefromAlbum
                // $message['EventKey'] = 'qrscene_sharefrom_9_45';
                // $message['EventKey'] = 'qrscene_sharefrom_9';
                //(int)filter_var($message['EventKey'], FILTER_SANITIZE_NUMBER_INT);//qrscene_sharefrom_2
                if (! $this->userIsNew) {
                    $keyword = 'qrscene_resubscribe';
                    $content = "[撇嘴]欢迎老朋友回来\n[衰]重复扫码关注助力无效\n[抱拳]回复【600】获取节目菜单\n/:strong回复不带【中括号】";
                    if ($albumId) {
                        $content .= "\n🎉回复【{$albumIndex}】和朋友组团挑战";
                    }
                    if ($user && $user->subscribe != 1) {
                        $user->toggleSubscribe();
                    }
                } else {
                    $keyword = 'qrscene_subscribe';
                    // $user 被推荐的用户
                    if ($user->user_id != $recommenderId) {
                        $user->user_id = $recommenderId;
                        if ($user->subscribe != 1) {
                            $user->subscribe = 1;
                        }
                        $user->save();
                        //积分+通知!
                        if (! $albumId) {
                            //活动推荐和永久推荐
                            WechatUserRecommendQueue::dispatch($recommenderId)->delay(now()->addSeconds(3));
                        } else {
                            // 专辑推荐, 计算3个用户即可成功免费获取! cache for 3 users!!!!
                            // 不加积分,只计算个数3 1.获取订阅id,2.获取count,3.比较counts
                            WechatUserRecommendQueue::dispatch($recommenderId, $albumId, $user->id)->delay(now()->addSeconds(3));
                        }
                    }
                    //增加
                    $content = "[鼓掌]谢谢关注,终于等到你!\n/:strong回复【600】获取节目菜单\n回复【500】获取节目菜单\n/:heart回复不带【中括号】\n[强]永不止息,需要有你";
                    if ($albumId) {
                        $content = "[鼓掌]助力好友挑战成功\n/:strong回复【600】获取免费节目菜单\n/:heart回复不带【中括号】\n🎉回复【{$albumIndex}】和组团挑战";
                    }

                    //重新发送专辑订阅 第一个图文
                    if ($albumId) {
                        $res = $album->toWechat();
                        $subscribeType = Album::class;
                        $subscribeId = $albumId;
                        $this->subscribeCache->put($this->subscribeTypeCacheKey, $subscribeType, 720);
                        $this->subscribeCache->put($this->subscribeIdCacheKey, $subscribeId, 720);

                        $this->commentCache->put($this->commentTypeCacheKey, Post::class, 720);
                        $this->commentCache->put($this->commentIdCacheKey, $res['comment_id'], 720); //$firstPost->id

                        $res['custom_message'] = $content;
                        $res['ga_data']['category'] = $keyword;
                    }
                }
            } else { //其他关注!
                // $keyword = 'subscribe';
                if ($this->isMainApp) {
                    if ($user->subscribe != 1) {
                        $keyword = 'resubscribe';
                        $user->toggleSubscribe();
                    }
                }
                $res = $this->autoReply($this->toUserName, $keyword);
            }
        }

        if ($event == 'unsubscribe') {
            $content = 'user unsubscribed';
            if ($this->isMainApp) {
                if ($user->subscribe != 0) {
                    $user->toggleSubscribe();
                }
                Log::error('unsubscribe', [$user->id, $message['FromUserName']]);
            }
        }

        if (! $res) {
            if (! $content) {
                $content = $event; //$message['Event'];
                //TEMPLATESENDJOBFINISH
                //MASSSENDJOBFINISH
            }
            $res = [
                'type'    => 'text',
                'content' => $content,
                'ga_data' => [
                    'category' => $keyword,
                    'action'   => $gaAction,
                ],
            ];
        }
        if ($res) {
            return $this->finalReply($keyword, $res);
        } else {
            Log::error(__CLASS__, [__FUNCTION__, __LINE__, '没有res?', $keyword, $message]);
        }
    }

    protected function _comment()
    {
        $res = false;
        $commentType = $this->commentCache->get($this->commentTypeCacheKey);
        $commentId = $this->commentCache->get($this->commentIdCacheKey);
        if ($commentId == 0) {
            $content = "[抱拳]641-645 不可评论!\n请回复【500】订阅良院课程\n获取节目后再评论";
            $res = [
                'type'    => 'text',
                'content' => $content,
                'ga_data' => [
                    'category' => 'comment',
                    'action'   => 'cannot',
                ],
            ];

            return $res;
        }
        //上次获取的节目类型和id
        if ($commentType && $commentId) {
            if (in_array($commentType, [LyMeta::class, LyLts::class])) {
                $codePlayAt = explode(',', $commentId);
                $targetId = $codePlayAt[0];
                $playAt = $codePlayAt[1];
                $model = LyAudio::firstOrCreate(
                    [
                        'target_id'    => $targetId,
                        'target_type'  => $commentType,
                        'play_at'      => $playAt,
                    ]
                );
                if ($commentType == LyLts::class) {
                    $lyLts = lyLts::find($targetId); //1-52,180101
                    $model->excerpt = '《'.$lyLts->name.'》 第'.$playAt.'课';
                    $model->save();
                    Log::info('NewLtsAudioForComment', [$model->id, $commentType, $commentId]);
                }
            }

            if (in_array($commentType, [Album::class, Post::class])) {
                $model = Post::where('id', $commentId)->first();
            }
            if ($commentType == LyAudio::class) {
                $model = LyAudio::where('id', $commentId)->first();
            }

            $this->user->comment($model, $this->keyword);

            // http://html.test/LyAudio/802
            $modelName = (new ReflectionClass($model))->getShortName();
            $id = $model->id;
            // $link = config('app.url'). "/$modelName/$id";
            $link = URL::route("{$modelName}.show", ['slug'=>$model->slug]);
            $content = "<a href='{$link}#comments'>评论成功！</a>";

            // region 加积分
            // 每发布一条内容, 奖励50积分(每日最高可积1000分).
            $this->pointsCache = Cache::tags('points');
            $points = $this->pointsCache->get($this->pointsCommentCacheKey);
            if ($points < User::POINT_COMMENT_DAY_LIMIT) {
                $this->pointsCache->put($this->pointsCommentCacheKey, $points + 50, 24 * 60);
                $amount = 50; // (Double) Can be a negative value
                $message = "发布评论: $link";
                $this->user->addPoints($amount, $message);
                $content .= "\n恭喜您,本次评论得".User::POINT_PRE_COMMENT.'积分!';
            } else {
                $content .= "\n恭喜您,今日得满分!";
            }
            $content .= "\n现在总积分:".$this->user->currentPoints().'积分!';
            // endregion
            $res = [
                'type'    => 'text',
                'content' => $content,
                'ga_data' => [
                    'category' => 'comment',
                    'action'   => $modelName.'_'.$id,
                ],
            ];

            return $res;
        }
    }

    protected function _new_subscription($subscribeType, $subscribeId, $price = 0, $sendAt = 6, $album = null)
    {
        $subscription = [
            'user_id'           => $this->user->id,
            'target_type'       => $subscribeType,
            'target_id'         => $subscribeId,
            'wechat_account_id' => $this->wechatAccount['id'],
            'price'             => $price,
        ];
        if ($album && $album->rrule) {
            $subscription['rrule'] = $album->rrule;
        }
        $subscription = AlbumSubscription::firstOrCreate($subscription);
        if ($subscription) {
            $subscription->send_at = $sendAt;
            // make sure the subscription is active.
            if ($subscription->active == false) {
                $subscription->active = true;
            }
            $subscription->save();

            $res = [
                'type'    => 'text',
                //每天".$sendAt."点左右
                'content' => "[握手]恭喜，订阅成功！\n/:strong我们将向您推送订阅内容\n[抱拳]您可以随时回复【退订".$subscription->id.'】退订！',
                'ga_data' => [
                    'category' => 'album_subscription',
                    'action'   => $subscribeType.'_'.$subscribeId,
                ],
            ];
            $this->subscribeCache->flush();

            return $res;
        }
    }

    protected function _free_limit()
    {
        $subscription = AlbumSubscription::where('user_id', $this->user->id)
            ->where('price', 0)
            ->where('active', 1)
            ->first();
        $donateUrl = Wechat::get_donate_url();
        $content = "[心碎]对不起，因资源紧张，订阅数量已达上限\n[抱拳]您可以回复【退订".$subscription->id."】后，再回复【订阅】即可\n[爱心]若有感动<a href='".$donateUrl."'>请点此赞助支持永不止息</a>[抱拳]";
        $res = [
            'type'    => 'text',
            'content' => $content,
            'ga_data' => [
                'category' => 'album_subscription',
                'action'   => 'out_of_limit',
            ],
        ];

        return $res;
    }

    protected function _subscribe()
    {
        $res = null;
        //专辑订阅之前cache 专辑701-7991
        if (preg_match('/^7(\d{2,})/', $this->keyword, $matches)) {
            if ($matches && isset($matches[1])) {
                $subscribeId = $matches[1];
                //add cache for 70?
                $AlbumCache = Cache::tags('album701');
                $res = $AlbumCache->get($subscribeId);
                $subscribeType = Album::class;
                if (! $res) {
                    $album = Album::find($subscribeId);
                    if ($album) {
                        $res = $album->toWechat();
                        $userId = $this->user->id;
                        $subscription = AlbumSubscription::where('user_id', $userId)
                            ->where('target_type', $subscribeType)
                            ->where('target_id', $subscribeId)
                            ->first();
                        $domain = Upyun::DOMAIN.'/videos/2019/faq';
                        $description = "菜单点击[一键订阅]↘️\n或回复【订阅】即可\n<a href='{$domain}/如何订阅和退订.mp4'>====>订阅帮助</a>\n👇点击查看第1集";
                        if ($subscription && $subscription->price > 0 && $subscription->active == 1) {
                            $description = "您已订阅无需重复订阅,\n回复【退订{$subscription->id}】即可退订";
                        }

                        $res['custom_message'] = $description;

                        $AlbumCache->put($subscribeId, $res, 7200);
                    }
                }

                if (! $res) {
                    //700菜单
                    $albums = Album::active()->inRandomOrder()->take(10)->get();
                    $content = '';
                    foreach ($albums as $album) {
                        $albumIndex = $album->getIndex();
                        $content .= "【{$albumIndex}】{$album->title} | ¥".$album->price."\n";
                    }//订阅推送:
                    $content .= "\n所有资源免费,分享免费,\n价格为推送费用.\n回复对应编号即可订阅.";
                    $res = [
                        'type'    => 'text',
                        'content' => $content,
                        'ga_data' => [
                            'category' => 'album_menu',
                            'action'   => 'get',
                        ],
                    ];
                } else {
                    $this->subscribeCache->put($this->subscribeTypeCacheKey, $subscribeType, 720);
                    $this->subscribeCache->put($this->subscribeIdCacheKey, $subscribeId, 720);

                    // $this->commentCache->put($this->commentTypeCacheKey, Post::class, 720);
                    // $this->commentCache->put($this->commentIdCacheKey, $res['comment_id'], 720);//$firstPost->id
                }

                return $res;
            }
        }
        //endregion

        $userId = $this->user->id;
        $FreeCounts = $this->user->get_free_subscription_counts();
        if (in_array($this->keyword, ['订阅', '定阅', 'dingyue'])) {
            $subscribeType = $this->subscribeCache->get($this->subscribeTypeCacheKey);
            $subscribeId = $this->subscribeCache->get($this->subscribeIdCacheKey);
            //增加记录订阅！如果有，提示啊哦，您已订阅，无需再次订阅！恭喜，订阅成功。
            if ($subscribeType && $subscribeId) {
                $subscription = AlbumSubscription::where('user_id', $userId)
                    ->where('target_type', $subscribeType)
                    ->where('target_id', $subscribeId)
                    ->first();
                if ($subscription && $subscription->active == true) {
                    $content = "[抱拳]您已订阅无需重复订阅\n回复【退订{$subscription->id}】即可退订";
                    if ($subscription->price < 0) {
                        $order = WechatPayOrder::where('user_id', $userId)
                            ->where('target_type', $subscribeType)
                            ->where('target_id', $subscribeId)
                            ->first();
                        $album = Album::find($subscription->target_id);
                        $link = config('app.url').'/wxpay/'.$order->id;
                        $albumIndex = $album->getIndex();
                        $content = "价值{$album->ori_price}元 现仅:¥{$album->price}\n<a href='$link'>请点击此链接完成支付</a>\n<a href='$link'>即可每日获取更新推送</a>\n=====免费福利=====\n分享专属海报, 即刻免费拥有\n在2小时内将海报二维码发送给身边朋友（达成3人扫码关注，即可免费学习）\n回复【客服】加客服微信";

                        WechatPosterQueue::dispatch($this->user, true, $subscribeId);
                    }

                    return [
                        'type'    => 'text',
                        'content' => $content,
                        'ga_data' => [
                            'category' => 'album_resubscription',
                            'action'   => $subscribeType.'_'.$subscribeId,
                        ],
                    ];
                }
                //专辑
                if ($subscribeType == Album::class) {
                    //判断是否是付费专辑!
                    $album = Album::find($subscribeId);
                    // $albumIndex = $album->getIndex();
                    $sendAt = '18';
                    // $sendAt = array_random(AlbumSubscription::RANDOM_SEND_AT);//[6,12,18...]
                    if ($album->price > 0) {
                        //返回购买链接 buy/album/1
                        //生产订单,提交订单,支付跳转!
                        $fee = $album->price;
                        $outTradeNo = config('wechat.payment.default.mch_id').'|'.date('YmdHis').'|'.$userId;
                        $order = WechatPayOrder::Create([
                            'user_id'       => $userId,
                            'target_type'   => $subscribeType,
                            'target_id'     => $subscribeId,
                            'description'   => '订阅专辑',
                            'out_trade_no'  => $outTradeNo,
                            'total_fee'     => $fee,
                            'trade_type'    => 'JSAPI',
                        ]);
                        $link = config('app.url').'/wxpay/'.$order->id;

                        $res = [
                            'type'    => 'text',
                            'content' => "资源免费, 分享免费\n订阅收取推送费\n价值{$album->ori_price}元 现仅:¥{$album->price}(2小时后恢复原价)\n<a href='$link'>请点击此链接完成支付</a>\n=====免费福利=====\n分享专属海报, 即刻拥有\n2小时内邀请3人成功关注即可", //
                            'ga_data' => [
                                'category' => 'album_subscription',
                                'action'   => $subscribeType.'_'.$subscribeId,
                            ],
                        ];
                        $this->_new_subscription($subscribeType, $subscribeId, -$fee, $sendAt, $album);
                        WechatPosterQueue::dispatch($this->user, true, $subscribeId);

                        return $res;
                    } else {
                        if ($FreeCounts >= AlbumSubscription::FREE_COUNT_LIMIT) {
                            return $this->_free_limit();
                        } else {
                            return $this->_new_subscription($subscribeType, $subscribeId, 0, $sendAt, $album);
                        }
                    }
                }

                // todo membership
                if ($FreeCounts >= AlbumSubscription::FREE_COUNT_LIMIT) {
                    return $this->_free_limit();
                }
                // endregion

                if (in_array($subscribeType, [LyMeta::class, LyLts::class])) {
                    $codePlayAt = explode(',', $subscribeId); //52,180901
                    $subscribeId = $codePlayAt[0];
                    // region 免费资源最多订阅3个
                    $sendAtTime = AlbumSubscription::RANDOM_SEND_AT; //[6,12,18...]
                    $sendAt = $sendAtTime[$FreeCounts];

                    return $this->_new_subscription($subscribeType, $subscribeId, 0, $sendAt);
                }
            } else {
                $res = [
                    'type'    => 'text',
                    'content' => "操作有误,请重新回复编号后再进行操作!\n回复【600】获取可免费节目\n回复【500】获取可免费节目\n回复【】内编号获取相应资源\n回复不带【中括号】",
                    'ga_data' => [
                        'category' => 'album_subscription',
                        'action'   => 'no_cache',
                    ],
                ];

                return $res;
            }
        }

        if (in_array($this->keyword, ['一键退订', '取消订阅', '取消定阅'])) {
            $this->user->unSubscribeAll();
            $content = "[抱拳]您已取消所有订阅，再见\n👋永不止息，感恩有你！";

            return [
                'type'    => 'text',
                'content' => $content,
                'ga_data' => [
                    'category' => 'album_unSubscribeAll',
                    'action'   => $this->user->id,
                ],
            ];
        }

        if (str_contains($this->keyword, '退订') && preg_match('/\d{1,}/', $this->keyword)) {
            //todo 您已订阅，无需重复订阅！
            preg_match('/退订(\d{1,})/', $this->keyword, $matches);
            if ($matches && isset($matches[1])) {
                $subscribeId = $matches[1];
                /* @var $sub App\Models\AlbumSubscription */
                $subscription = AlbumSubscription::find($subscribeId);
                if ($subscription
                    && $subscription->user_id == $this->user->id
                    && $subscription->active == true) {
                    $subscription->active = false;
                    $subscription->save();
                    $res = [
                        'type'    => 'text',
                        'content' => "[再见]退订成功！悄悄的我走了\n[抱拳]永不止息，感恩有你",
                        'ga_data' => [
                            'category' => 'album_unsubscription',
                            'action'   => $subscription->id,
                        ],
                    ];
                } else {
                    $res = [
                        'type'    => 'text',
                        'content' => "[心碎]啊哦，出错啦[抱拳]\n回复【700】获取可付费订阅\n回复【600】获取可免费节目\n回复【500】获取可免费节目\n回复【】内编号获取相应资源\n回复不带【中括号】",
                        'ga_data' => [
                            'category' => 'error',
                            'action'   => 'album_unsubscription',
                        ],
                    ];
                }
            }
        }

        return $res;
    }

    protected function _recommend()
    {
        $res = null;
        $user = $this->user;
        $openId = $this->openId;
        $res = [
            'type'    => 'text',
            'content' => '出错了',
            'ga_data' => [
                'category' => '_recommend',
                'action'   => $user->id.'_'.$this->openId,
            ],
        ];

        if (is_null($user->profile) || ! isset($user->profile->headimgurl)) {
            WechatUserProfileQueue::dispatch($user);
            $res['content'] = '活动火爆,请5秒后再试';

            return $res;
        }

        //临时和永久推荐码
        // $isTemporary = $this->keyword == '活动'??false;
        $isTemporary = true;
        if ($isTemporary) {
            $cacheKey = $openId.'_tmp';
        } else {
            $cacheKey = $openId.'_forever';
        }
        if ($this->keyword == '活动') {
            $cacheKey .= '_activity';
        } else {
            //推荐
            $cacheKey .= '_recommend';
        }
        $cacheTag = WechatPosterQueue::CACHE_TAG;
        $cache = Cache::tags($cacheTag);
        $mediaId = $cache->get($cacheKey);
        if ($mediaId) {
            $res['type'] = 'image';
            $res['content'] = $mediaId;

            return $res;
        } else {
            WechatPosterQueue::dispatch($this->user, $isTemporary, false, $this->keyword);
            $res['content'] = "[咖啡]正在为您制作二维码海报\n[抱拳]请稍等片刻...";
            if ($isTemporary) {
                $res['content'] .= "\n======您知道吗?======\n由于微信限制,30天后请回复【{$this->keyword}】刷新二维码";
            } else {
                $res['content'] .= "\n感谢您宣传永不止息\n您可以通过朋友圈,面对面分享给您的好朋友\n永不止息,感恩有你!";
            }

            return $res;
        }
    }
}
