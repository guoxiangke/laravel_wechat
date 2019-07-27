<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/2
 * Time: 下午2:22.
 */

namespace App\Services\Wechat;

use App\Jobs\WechatMessageQueue;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

class MessageLogHandler implements EventHandlerInterface
{
    public function handle($message = null)
    {
        WechatMessageQueue::dispatch($message)->delay(now()->addSeconds(6));
    }
}
