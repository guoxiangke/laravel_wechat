<?php

use App\Models\WechatAutoReply;
use Illuminate\Database\Seeder;

class AutoRepliesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tmpData = [
            'name'         => 'PingPong',
            'to_user_name' => 'gh_all',
            'type'         => 'Text',
            'patten'       => 'ping',
            'content'      => 'pong',
        ];
        WechatAutoReply::firstOrNew($tmpData)->save();

        $content = <<<'EFO'
欢迎您关注【良友知音】微信订阅号。本订阅号可收听良友电台节目、了解电台最新节目动向和各类活动消息、与电台互动。每日为您推送灵修节目《旷野吗哪》声档和文章，选推各类精彩节目图文~~
<a href="http://t.cn/Rk9RyUH">点此可查看历史图文</a>
还可以发送节目代号，获取音频，收听几十档有关信仰、生活、婚姻、家庭、教养子女等各类的节目。【良友知音】盼望与您“知音、牵手、同行”，一起做“指尖的福音分享”，透过一个简单的分享动作把福音、爱、希望分享给更多的人。
发送数字0可获取 “发送代号”，查看每个节目相对应的代号，观看“良友知音”收听节目与分享节目的操作指南。
更多功能请点击下方菜单栏
EFO;

        $tmpData = [
            'name'         => '知音订阅hello消息',
            'to_user_name' => 'gh_fb86cb40685c',
            'type'         => 'Text',
            'patten'       => 'subscribe',
            'content'      => $content,
        ];
        WechatAutoReply::firstOrNew($tmpData)->save();
        $tmpData = [
            'name'         => '知音默认图文消息',
            'to_user_name' => 'gh_fb86cb40685c',
            'type'         => 'News',
            'patten'       => 'default',
            'content'      => "[{\"title\":\"\u6536\u542c\u8282\u76ee\u4ee3\u53f7\",\"description\":\"\u6b22\u8fce\u6536\u542c\u6211\u4eec\u7684\u8282\u76ee\uff01\u77e5\u97f3\u2027\u7275\u624b\u2027\u540c\u884c \u203b \u4e3a\u4f60\u9001\u4e0a\u7cbe\u5f69\u8282\u76ee\u548c\u6d3b\u52a8\uff01\",\"image\":\"https:\/\/mmbiz.qlogo.cn\/mmbiz\/JkVibryc6qrY4zkXwy5SPZbur1Jd7HMRneGCr0xhsqEJFXyQ0XkTqZmG8xCPIadhCt4GJk0kibtevrUIJ2Wpw7ag\/0?wx_fmt=jpeg\",\"url\":\"https:\/\/mp.weixin.qq.com\/s\/kcFesDf3xZC-dVtM-IdpKA\"}]",
        ];
        WechatAutoReply::firstOrNew($tmpData)->save();

        $tmpData = [
            'name'         => '知音默认非数字转客服',
            'to_user_name' => 'gh_fb86cb40685c',
            'type'         => 'Transfer',
            'patten'       => '\D+',
            'weight'       => 1,
        ];
        WechatAutoReply::firstOrNew($tmpData)->save();
        $content = <<<'EFO'
每邀请1个新人关注,赠送1000积分, (每1000积分可在结账时可抵现1元). 最高免50%! 赶快邀请好友支援吧!
每收听一个节目. 奖励10积分(每天最高可积100分).
每发布一条内容, 奖励50积分(每日最高可积1000分).
积分可以到积分商城兑换礼品.
积分达到一定额度可提现(100元起).
提现以红包的形式退送给你的微信零钱/钱包!
EFO;
        $tmpData = [
            'name'         => '积分规则',
            'to_user_name' => 'gh_c2138e687da3',
            'type'         => 'Text',
            'patten'       => '积分',
            'content'      => $content,
        ];
        WechatAutoReply::firstOrNew($tmpData)->save();
    }
}
