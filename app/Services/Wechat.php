<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/8/30
 * Time: 上午8:47.
 */

namespace App\Services;

use App\Jobs\GampQueue;
use App\Models\WechatAccount; //红包发送记录
use App\Models\WechatRedpack;
use EasyWeChat;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Music;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Transfer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
// use Overtrue\Pinyin\Pinyin;
// use WechatDev\WechatJSSDK;
use Illuminate\Support\Facades\Log;

class Wechat
{
    const MAIN_APP = 'gh_c2138e687da3'; //认证服务号
    const TEST_OPEN_ID = 'oTjEws-8eAAUqgR4q_ns7pbd0zN8'; //小永
    const THUMB_MEDIA_ID = '0EiLlKqUqHoIZkYcahv0yy10zHDdyfGOvzTIlUtiJHE';

    //小永微助理 二维码:
    // "media_id" => "0EiLlKqUqHoIZkYcahv0y-4RLUfoVdDo2gCKhCC-V7A"
    // "url" => "http://mmbiz.qpic.cn/mmbiz_jpg/dTE2nNAecJYF9EabBqziaU3OlrySsyqvOwWQBibELGzFib0Tu07DPZico2VtwPFvHtotjxX2CwVQIVna89gicGUy7Gg/0?wx_fmt=jpeg"

    public static function init($wechatAccount = 1)
    {
        if (is_int($wechatAccount)) {
            $wechatAccount = WechatAccount::find($wechatAccount);
        }
        if ($wechatAccount->token == self::MAIN_APP) {
            return app('wechat.official_account');
        }
        $config = [];
        $config['app_id'] = $wechatAccount->app_id;
        $config['secret'] = $wechatAccount->secret;
        $config['token'] = $wechatAccount->token;
        $config['aes_key'] = $wechatAccount->aes_key;
        $logPath = env('WECHAT_LOG_FILE', storage_path('logs/'.$wechatAccount->token.'.log'));
        $config['log'] = [
            'default'  => Config::get('app.env'), // 默认使用的 channel，生产环境可以改为下面的 prod
            'channels' => [
                'development' => [
                    'driver' => 'single',
                    'path'   => $logPath,
                    'level'  => 'debug',
                ],
                'staging' => [
                    'driver' => 'single',
                    'path'   => $logPath,
                    'level'  => 'debug',
                ],
                'production' => [
                    'driver' => 'daily',
                    'path'   => $logPath,
                    'level'  => 'error',
                ],
            ],
        ];
        /* @var $app \EasyWeChat\officialAccount\Application */
        $app = Factory::officialAccount($config);

        return $app;
    }

    public static function replyByType($type, $content)
    {
        switch ($type) {
            case 'news':
                $items = [];
                if (is_string($content)) {
                    foreach (json_decode($content, 1) as $item) {
                        $items[] = new NewsItem($item);
                    }
                } else {
                    $items[] = new NewsItem($content);
                }

                return new News($items);
                break;
            case 'image':
                return new Image($content);
                break;
            case 'text':
                return new Text($content);
                break;
            case 'music':
                return new Music($content);
                break;
            case 'transfer':
                return new Transfer();
                break;
            default:
                return;
        }
    }

    /**
     * [customMessage only text].
     *
     * @param [type] $res    [description]
     * @param [type] $app    [description]
     * @param [type] $openId [description]
     *
     * @return [type] [description]
     */
    public static function customMessage($res, $app, $openId)
    {
        /* @var $app \EasyWeChat\officialAccount\Application */
        //$app = $this->app;
        // $message = new Text(str_limit($res['custom_message'], 200, '...'));
        $customRes['type'] = 'text';

        //[604]+节目摘要
        if (isset($res['custom_message'])) {
            $message = $res['custom_message'];
            $customRes['content'] = $message;

            return static::custom($customRes, $app, $openId);
        }

        if (isset($res['custom_messages'])) {
            $messages = '';
            foreach ($res['custom_messages'] as $message) {
                $messages .= $message."\n";
            }
            $messages .= "=========\n".static::getRandomFaq();
            $customRes['content'] = $messages;

            return static::custom($customRes, $app, $openId);
        }
    }

    /**
     * 发送客服消息.
     *
     * @param [type] $custom_res [description]
     * @param [type] $app        [description]
     * @param [type] $openId     [description]
     *
     * @return [type] [description]
     */
    public static function custom($res, $app, $openId)
    {
        $message = self::replyByType($res['type'], $res['content']);

        return $app->customer_service->message($message)->to($openId)->send();
    }

    public static function gaPush($res, $wechatAccount, $toUserName)
    {
        // https://packagist.org/packages/theiconic/php-ga-measurement-protocol
        // https://github.com/irazasyed/laravel-gamp
        //GAMP_ASYNC=false not work 使用队列 替换
        $clientId = $toUserName;
        $category = $res['ga_data']['category'];
        $action = $res['ga_data']['action'];
        $label = $wechatAccount->name;
        GampQueue::dispatch($clientId, $category, $action, $label)->delay(now()->addSeconds(5));
    }

    const jsApiList = [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareQZone',
        'hideMenuItems',
        'showMenuItems',
        'hideAllNonBaseMenuItem',
        'showAllNonBaseMenuItem',
        'translateVoice',
        'startRecord',
        'stopRecord',
        'onVoiceRecordEnd',
        'playVoice',
        'onVoicePlayEnd',
        'pauseVoice',
        'stopVoice',
        'uploadVoice',
        'downloadVoice',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'getNetworkType',
        'openLocation',
        'getLocation',
        'hideOptionMenu',
        'showOptionMenu',
        'closeWindow',
        'scanQRCode',
        'chooseWXPay',
        'openProductSpecificView',
        'addCard',
        'chooseCard',
        'openCard',
    ];

    public static function get_wechat_qr_url($userId)
    {
        $cache = Cache::tags('wechat_qr');
        $cacheKey = 'wechat_qr_'.$userId;
        $res = $cache->get($cacheKey);
        if (!$res) {
            /* @var $app \EasyWeChat\officialAccount\Application */
            $app = static::init(1);
            $result = $app->qrcode->temporary('sharefrom_'.$userId);
            $res = $result['url'];
            $cache->put($cacheKey, $res, now()->addMinutes(43200)); // 30天后过期
        }

        return $res;
    }

    public static function get_donate_url()
    {
        return 'https://wechat.yongbuzhixi.com/donate';
    }

    // public static function is_pinyin($keyword='订阅'){
    //     $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
    //     $keywods = [];
    //     $keywods[] = $pinyin->convert($keyword);//dingyue
    //     $keywods[] = $pinyin->abbr($keyword);//dy
    //     if(in_array($keyword, $keywods)){
    //         return true;
    //     }
    //     return false;
    // }

    public static function getSignPackage($currentUrl)
    {
        $wechatJSSDK = new WechatJSSDK(config('wechat.official_account.default.app_id'), config('wechat.official_account.default.secret'));
        //env('WECHAT_OFFICIAL_ACCOUNT_APPID'),env('WECHAT_OFFICIAL_ACCOUNT_SECRET')
        return $wechatJSSDK->getSignPackage($currentUrl);
    }

    public static function getRandomFaq()
    {
        $FaqRandom = array_random(
          [
            [
              'title'=> '如何解除套餐提醒?',
              'src'  => '解除套餐提醒限制.mp4',
            ],
            [
              'title'=> '如何获取消息?',
              'src'  => '如何获取消息.mp4',
            ],
            [
              'title'=> '如何订阅消息?',
              'src'  => '如何订阅和退订.mp4',
            ],
            [
              'title'=> '如何收听音频消息?',
              'src'  => '收听音频.mp4',
            ],
            [
              'title'=> '如何评论?',
              'src'  => '如何评论.mp4',
            ],
            [
              'title'=> '如何分享音频消息给朋友?',
              'src'  => '分享音频消息.mp4',
            ],
            [
              'title'=> '如何分享到朋友圈?',
              'src'  => '分享朋友圈.mp4',
            ],
            [
              'title'=> '错误的分享方式?',
              'src'  => '错误分享方式.mp4',
            ],
            [
              'title'=> '如何订阅和退订?',
              'src'  => '如何订阅和退订.mp4',
            ],
            [
              'title'=> '如何置顶云彩助手?',
              'src'  => '置顶.mp4',
            ],
            [
              'title'=> '如何暂停?',
              'src'  => '暂停.mp4',
            ],
            [
              'title'=> '如何浮窗播放?',
              'src'  => 'iphone浮窗.mp4',
            ],
            [
              'title'=> '如何调整图文字体大小?',
              'src'  => '字体大小.mp4',
            ],
            [
              'title'=> '如何关注云彩助手?',
              'src'  => '关注.mp4',
            ],
            [
              'title'=> '订阅有啥好处?',
              'src'  => '订阅功能好处.mp4',
            ],
            [
              'title'=> '啥叫耐心等待数秒?⌛️',
              'src'  => '耐心等待数秒.mp4',
            ],
          ]
        );
        $domain = Upyun::DOMAIN.'/videos/2019/faq';

        return "<a href='{$domain}/{$FaqRandom['src']}'>{$FaqRandom['title']}</a>";
    }

    // 单位为分，不小于100=1元,最低1元
    public static function sendRedpack($amount = 1, $openId = self::TEST_OPEN_ID)
    {
        $payment = EasyWeChat::payment(); // 微信支付
        $redpack = $payment->redpack;
        $redpackData = [
            'mch_billno'   => 'test'.date('YmdHis'),
            'send_name'    => '云彩助手',
            're_openid'    => $openId,
            'total_amount' => $amount * 100,
            'wishing'      => '永不止息,分享有礼,回复[推荐]获得更多红包.',
            'act_name'     => '感谢您的参与',
            'scene_id'     => 'PRODUCT_2',
        ];
        $result = $redpack->sendNormal($redpackData);
        $model = WechatRedPack::firstOrCreate($result); //红包发送记录
        Log::error(__FUNCTION__, [$result, $model]);
        // Notification::send($users, new WechatTemplateMessageSent($redpackData)); //微信模版通知记录
        return $result;
    }

    /**
     * 主动发送客服消息 without $app.
     *
     * @param [type] $custom_res [description]
     * @param [type] $app        [description]
     * @param [type] $openId     [description]
     *
     * @return [type] [description]
     */
    public static function send($res, $openId)
    {
        $app = EasyWeChat::officialAccount();
        $message = self::replyByType($res['type'], $res['content']);

        return $app->customer_service->message($message)->to($openId)->send();
    }

    // bug, 保证一个用户只有一个 profile // user_id = 2345 has 2 profles!!!
    // https://www.kancloud.cn/php-jdxia/laravel5note/388603
    public static function updateProfile($user) //$mpId, $openId, $userId
    {
        $userId = $user->id;
        $mpId = config('wechat.official_account.default.token');
        $openId = $user->name;

        // $wechatAccount = WechatAccount::where('to_user_name', $mpId)
        //     ->first();
        // $app = Wechat::init($wechatAccount);
        $app = EasyWeChat::officialAccount();
        $wxProfile = $app->user->get($openId);
        if (!is_array($wxProfile)) {
            return;
        }
        if (!isset($wxProfile['nickname'])) {
            return;
        }
        if (!isset($wxProfile['headimgurl'])) {
            return;
        }

        $wechatProfile = WechatUserProfile::where('user_id', $userId)->first();
        if ($wechatProfile) {
            //更新用户部分信息
            $old = $wechatProfile->toArray();
            if ($old['headimgurl'] != $wxProfile['headimgurl'] || $old['nickname'] != $wxProfile['nickname']) {
                $wechatProfile->fill($wxProfile);
                $wechatProfile->save();
            }
        } else {
            //新建用户信息
            $wxProfile['user_id'] = $userId;
            $wechatProfile = new WechatUserProfile();
            $wechatProfile->fill($wxProfile);
            $wechatProfile->save();
        }

        return $wechatProfile;
    }

    public static function getMenu($uid = 1)
    {
        $app = self::init($uid);
        $current = $app->menu->current();

        return collect($current['selfmenu_info']['button'])->map(function ($menu) {
            if (isset($menu['sub_button'])) {
                $menu['sub_button'] = $menu['sub_button']['list'];
            }

            return $menu;
        })->toArray();
    }

    public static function setMenu($uid = 1, $menu = null)
    {
        $menu = $menu ?: config('wechatmenu.'.$uid);
        $app = self::init($uid);
        $current = $app->menu->current();
        $res1 = $app->menu->delete();
        $res2 = $app->menu->create($menu);
        \Log::error(__FUNCTION__, [$current, $res1, $res2]);

        return $res2;
    }
}
