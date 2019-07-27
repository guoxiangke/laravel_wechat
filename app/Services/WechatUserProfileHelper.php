<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/9/10
 * Time: 下午2:36.
 */

namespace App\Services;

use App\Models\User;
use App\Models\WechatAccount;
use App\Models\WechatUserProfile;

class WechatUserProfileHelper
{
    // bug, 保证一个用户只有一个 profile // user_id = 2345 has 2 profles!!!
    // https://www.kancloud.cn/php-jdxia/laravel5note/388603
    public static function updateProfile($user) //$mpId, $openId, $userId
    {
        $userId = $user->id;
        $mpId = config('wechat.official_account.default.token');
        $openId = $user->name;

        $wechatAccount = WechatAccount::where('to_user_name', $mpId)
            ->first();
        $app = Wechat::init($wechatAccount);
        $wxProfile = $app->user->get($openId);
        if (! is_array($wxProfile)) {
            return;
        }
        if (! isset($wxProfile['nickname'])) {
            return;
        }
        if (! isset($wxProfile['headimgurl'])) {
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
            $wechatProfile = new WechatUserProfile;
            $wechatProfile->fill($wxProfile);
            $wechatProfile->save();
        }

        return $wechatProfile;
    }
}
