<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tmpData = [
            'name' => 'oTjEws-8eAAUqgR4q_ns7pbd0zN8',
            'password' => '$2y$10$YfUgrpsO9WxwGGprAwxZV.A5vBvCvsDbRTPIf5e4uFcJR9SJitiPi',
            'email' => 'oTjEws-8eAAUqgR4q_ns7pbd0zN8@wx'
        ];
        User::firstOrNew($tmpData)->save();
        DB::insert("INSERT INTO `wechat_user_profiles`(`user_id`, `openid`, `subscribe`, `nickname`, `sex`, `language`, `city`, `province`, `country`, `headimgurl`, `subscribe_time`, `unionid`, `remark`, `groupid`, `tagid_list`, `subscribe_scene`, `qr_scene`, `qr_scene_str`, `created_at`, `updated_at`) VALUES (1, 'oTjEws-8eAAUqgR4q_ns7pbd0zN8', '1', '永不止息-小永', 1, 'zh_CN', '东城', '北京', '中国', 'http://thirdwx.qlogo.cn/mmopen/QtMdPwEx5bCH9sjIBPtGSictFnwyhkPEMuKopQRKEYxicbwY623icOMNH41lpBOsHV5Dh441vP5XPvvp10F2ov0OLTwkIkONGyn/132', 1516955919, 'od0Q-xOt2anHlcA8KPJkAqQPCpP4', '', 0, NULL, 'ADD_SCENE_SEARCH', 0, '', '2018-09-09 00:06:13', '2018-09-09 00:06:13');");

    }
}
