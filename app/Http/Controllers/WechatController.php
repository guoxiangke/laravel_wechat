<?php

namespace App\Http\Controllers;

use App\Jobs\WechatUserProfileQueue;
use App\Models\User;
use App\Models\WechatAccount;
use App\Services\Wechat;
use App\Services\Wechat\LinkMessageHandler;
use App\Services\Wechat\LyMessageReplyHandler;
use App\Services\Wechat\MessageLogHandler;
use App\Services\Wechat\MessageReplyHandler;
use EasyWeChat\Kernel\Messages\Message;
use Illuminate\Support\Facades\Auth;

class WechatController extends Controller
{
    /**
     * 处理微信的请求消息.
     *
     * @return string
     */
    public function serve($toUserName = 'gh_aa9c2e621082')
    {
        $wechatAccount = WechatAccount::where('to_user_name', $toUserName)->first();
        //初始化 by profile or default profile.
        if ($wechatAccount) {
            /* @var $app \EasyWeChat\officialAccount\Application */
            $app = Wechat::init($wechatAccount);
        } else {
            /* @var $app \EasyWeChat\officialAccount\Application */
            $app = app('wechat.official_account');
        }
        /* @var $server \EasyWeChat\Kernel\ServerGuard */
        $server = $app->server;

        //默认回复！
        $server->push(function ($message) {
            return "[撇嘴]貌似哪里不对劲[衰] \n[Yeah!]评论时,内容不得少于8个字\n/:strong回复【】内编号即可,不带【】\n[抱拳]<a href='https://wechat.yongbuzhixi.com/docs'>常见问题,使用帮助</a>";
        });

        $message = $server->getMessage();
        //第一次微信验证token没有msg_id，不记录！
        if (is_array($message) && isset($message['MsgType'])) {
            $server->push(MessageReplyHandler::class);
            /*
            //良友知音定制回复
            // $server->push(LyMessageReplyHandler::class);
            # 队列处理历史消息记录 和 mp_limit
            if(isset($message['MsgId'])) {
                $server->push(MessageLogHandler::class);
                //only for 1
                if($message['ToUserName'] == Wechat::MAIN_APP){
                    // $server->push(LinkMessageHandler::class, Message::LINK);
                    // $server->push(EventMessageHandler::class, Message::EVENT);
                }
            }*/
        }
        $response = $server->serve();

        return $response;
    }

    /**
     * 微信自动登录网页.
     */
    public function login()
    {
        $wechatUser = session('wechat.oauth_user.default');
        $openId = $wechatUser['id'];
        $user = User::where('name', $openId)->first();
        if (!$user) {
            $user = User::newUser($openId);
            WechatUserProfileQueue::dispatch($user)->delay(now()->addSeconds(10));
        }
        if ($user) {
            Auth::login($user);
        }

        return redirect()->intended('home');
    }
}
