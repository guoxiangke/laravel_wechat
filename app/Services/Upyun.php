<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/29
 * Time: 下午10:49.
 */

namespace App\Services;

class Upyun
{
    const TTL = 3600; //1小时后过期

    const KEY = 'ly729';

    const DOMAIN = 'https://ybzx2018.yongbuzhixi.com';
    // const ONE_DOMAIN = 'https://1drive404.now.sh';
    const ONE_DOMAIN = 'https://oneybzx.yongbuzhixi.com';

    const IMAGE_CND_PREFIX = 'https://images.weserv.nl/?url=';

    /**
     * @param $path 图片相对路径
     * @param int    $time 授权1分钟后过期
     * @param string $key
     *
     * @return string token 防盗链密钥
     */
    public static function sign($path, $time = self::TTL, $key = self::KEY)
    {
        $time = time() + $time; // 授权1分钟后过期

        return '?_upt='.substr(md5($key.'&'.$time.'&'.$path), 12, 8).$time;
    }
}
