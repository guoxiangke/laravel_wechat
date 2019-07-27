<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/8/22
 * Time: 上午10:31
 * TODO: delete!!!
 */

namespace App\Services\Wechat;

use App\Services\Wechat;
use App\Models\WechatAccount;
use Illuminate\Support\Facades\Log;
use EasyWeChat\Kernel\Messages\Image;
use Illuminate\Support\Facades\Cache;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

class LyMessageReplyHandler implements EventHandlerInterface
{
    protected $msgType;
    protected $toUserName;
    protected $keyword;
    protected $appCopyName;
    protected $isLyApp = false;
    protected $isCertified = false;
    protected $openId = false;
    protected $app = false;

    public function handle($message = null)
    {
        $reply = null;
        $this->msgType = $message['MsgType'];
        $this->toUserName = $message['ToUserName'];

        $wechatAccount = WechatAccount::where('to_user_name', $this->toUserName)
            ->firstOrFail();
        /* @var $app \EasyWeChat\officialAccount\Application */
        $this->app = Wechat::init($wechatAccount);
        $this->appCopyName = '公众号:'.$wechatAccount->name;
        $this->isCertified = $wechatAccount->is_certified ? true : false;
        if ($wechatAccount->name == '良朋益友') {
            $this->isLyApp = true;
        }
        $this->openId = $message['FromUserName'];

        if ($this->msgType == 'text') {
            $keyword = strip_tags($message['Content']);
            $keyword = trim($keyword);
            $this->keyword = $keyword;
            $lyCache = Cache::tags('lyaccount');
            if ($keyword == '金句' && $this->isCertified && $this->isLyApp) {
                $cachedKeyword = $this->toUserName.'_'.$this->keyword;
                $mediaId = $lyCache->get($cachedKeyword);
                if (! $mediaId) {
                    $image_file_path = storage_path().'/app/jinju/';
                    $image_file_path = $image_file_path.date('W').'.jpg';
                    if (! file_exists($image_file_path)) {
                        $this->app->customer_service->message('消息好像还没准备好，请加小永微信： 13520055900 告诉俺')->to('oTjEwsycJgEpiKTzzisTRa8RP8y4')->send();
                    }
                    set_time_limit(0);
                    //$return = $this->app->material->uploadImage($image_file_path); //永久
                    $return = $this->app->media->uploadImage($image_file_path); //临时 3day
                    if (isset($return['media_id'])) {
                        $mediaId = $return['media_id'];
                        $lyCache->set($cachedKeyword, $mediaId, 43200); //3day 60*24*3
                    } else {
                        $reply = [
                            'type'=>'text',
                            'ga_data'       => [
                                'category' => 'lyapi_ju',
                                'action'   => 'error',
                            ],
                            'content'=> '活动火爆，系统繁忙，请再试一次！[握手]',
                        ];

                        return '活动火爆，系统繁忙，请再试一次！[握手]';
                    }
                }

                Log::error('金句', [$mediaId]);
                // todo
                $reply = [
                    'type' => 'image',
                    'ga_data'   => [
                        'category' => 'lyapi_ju',
                        'action'   => 'get',
                    ],
                    'content'=> $mediaId,
                ];

                return new Image($mediaId);
            }
        }
    }
}
