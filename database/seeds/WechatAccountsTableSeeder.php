<?php

use App\Models\WechatAccount;
use Illuminate\Database\Seeder;

class WechatAccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $resources = [
            'lymeta'   => true,
            'lylts'    => true,
            'subscribe'=> true,
        ];
        $tmpData = [
            'name'         => '云彩助手',
            'to_user_name' => 'gh_c2138e687da3',
            'app_id'       => 'wx07d8c7489700d878',
            'secret'       => '2cb26ee8323d99dc1f466c4fa507aecf',
            'token'        => 'gh_c2138e687da3',
            'aes_key'      => 'LHo8xjl7OhUdKYSaAJ2d0aFlVc6GiGE45zp7r4muco6',
            'is_certified' => true,
            'resources'    => $resources,
        ];
        WechatAccount::firstOrCreate($tmpData);
        $resources = [
            'lymeta'=> true,
            'lylts' => true,
        ];
        $tmpData = [
            'name'         => '永不止息',
            'to_user_name' => 'gh_e7d44148423c',
            'app_id'       => 'wx4e55fd23c29e1688',
            'secret'       => 'de221323ff657efcf528cbd966b06460',
            'token'        => 'gh_e7d44148423c',
            'aes_key'      => 'v2fdBtAI7AOfrjcJmWhvxK25K7fPtaI8YNKlyS1se9h',
            'is_certified' => true,
            'resources'    => $resources,
        ];
        WechatAccount::firstOrCreate($tmpData);

        $tmpData = [
            'name'         => '以琳婚恋',
            'to_user_name' => 'gh_aa9c2e621082',
            'app_id'       => 'wxcd4d79500885f523',
            'secret'       => '05785cd7a4001afe0e315895b002a64e',
            'token'        => 'gh_aa9c2e621082',
            'aes_key'      => 'NRdahrbT2mFYazNQaOm5lxtzD1Ifah8cumOAcsTFBny',
            'is_certified' => false,
            'resources'    => $resources,
        ];
        WechatAccount::firstOrCreate($tmpData);

        $menu = [
            [
                'name'      => '收听节目',
                'sub_button'=> [
                    [
                        'type'=> 'view',
                        'name'=> '使用说明',
                        'url' => 'http://mp.weixin.qq.com/s?__biz=MzI1MDE0NzM5Ng==&mid=403749894&idx=1&sn=0158a36a4c6d87aed8c7bad66a738791&scene=0&previewkey=gAXp7urAE47yC6BAt24jdMNS9bJajjJKzz/0By7ITJA=#wechat_redirect',
                    ],
                    [
                        'type'=> 'view',
                        'name'=> '发送代号',
                        'url' => 'https://mp.weixin.qq.com/s/K-42svN2Szljc-ReIrtS2g',
                    ],
                    [
                        'type'=> 'view',
                        'name'=> '同行频道',
                        'url' => 'http://txly1.net/',
                    ],
                    [
                        'type'=> 'view',
                        'name'=> '网络平台',
                        'url' => 'https://r.729ly.net/platforms',
                    ],
                ],
            ],
            [
                'name'      => '活动专题',
                'sub_button'=> [
                    [
                        'type'=> 'view',
                        'name'=> '专题系列',
                        'url' => 'http://mp.weixin.qq.com/mp/homepage?__biz=MzI1MDE0NzM5Ng==&hid=1&sn=8283a60d0f4c54d1e7f4523b733e5841#wechat_redirect',
                    ],
                    [
                        'type'    => 'miniprogram',
                        'name'    => '真道分解',
                        'url'     => 'http://mp.weixin.qq.com',
                        'appid'   => 'wx51087980eafff0b0',
                        'pagepath'=> 'pages/index/index',
                    ],
                    [
                        'type'=> 'view',
                        'name'=> '金句打卡',
                        'url' => 'http://mp.weixin.qq.com/s/q_EHthrawaaZNuUmG1chCQ',
                    ],
                    [
                        'type'=> 'view',
                        'name'=> '认识耶稣',
                        'url' => 'https://sway.com/iGhYjAbQfSU9nywM',
                    ],
                ],
            ],
            [
                'name'      => '关于良友',
                'sub_button'=> [
                    [
                        'type'=> 'view',
                        'name'=> '联络良友',
                        'url' => 'http://mp.weixin.qq.com/s?__biz=MzI1MDE0NzM5Ng==&mid=401923513&idx=1&sn=7834a54dbe4463796daf2773743fcead&scene=18#rd',
                    ],
                    [
                        'type'=> 'view',
                        'name'=> '转载须知',
                        'url' => 'https://mp.weixin.qq.com/s/SdLD7C1p5ChEnDDkyu5MYQ',
                    ],
                ],
            ],
        ];
        $new = [
          'type'    => 'miniprogram',
          'name'    => '真道分解',
          'url'     => 'http://mp.weixin.qq.com',
          'appid'   => 'wx51087980eafff0b0',
          'pagepath'=> 'pages/index/index',
        ];
        array_unshift($menu[1]['sub_button'], $new);
        unset($resources['lylts']);
        $tmpData = [
            'name'         => '良友知音',
            'to_user_name' => 'gh_fb86cb40685c',
            'app_id'       => 'wxbc774985efe8535b',
            'secret'       => '67f323f034d20f38f80e1c6951403065',
            'token'        => 'gh_fb86cb40685c',
            'aes_key'      => 'Rdnk9JQNZdm6udYh2VisCpJYo9BQtDGK6N7Jl7WKKJ2',
            'is_certified' => true,
            'menu'         => $menu,
            'resources'    => $resources,
        ];
        WechatAccount::firstOrCreate($tmpData);
    }
}
