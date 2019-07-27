<?php

namespace App\Services\Wechat;

use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

class LinkMessageHandler implements EventHandlerInterface
{
    public function handle($message = null)
    {
        $reply = null;

        // {"ToUserName":"gh_aa9c2e621082","FromUserName":"o2Vsiwdjb7pc-v1fYOtjsKABOUco","CreateTime":"1541411116","MsgType":"link","Title":"谁能逃离这辆车？","Description":"我们都在同一辆名为“互相伤害”的车上，谁能逃离？","Url":"http://mp.weixin.qq.com/s?__biz=MzAxMzcyMDY4Ng==&mid=2652606871&idx=1&sn=cc6851776f2461a02c4cbaf9c1f028ea&chksm=80717c96b706f580140f4730c7da64d6f000a61f58413d113e0c12565f14884b51cde27d88a7&mpshare=1&scene=2&srcid=1103thjcHClh4MAwL1YGFsAO#rd","MsgId":"6620310333344899927"}
    }
}
