<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/29
 * Time: 下午10:21.
 */

namespace App\Services\Wechat\Resources;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\LyLtsController;

class LtsHandle
{
    public static function process($oriKeyword)
    {
        $keyword = substr($oriKeyword, 0, 3);
        $offset = 0;
        if (strlen($oriKeyword) > 3) {
            $offset = substr($oriKeyword, 3);
        }//1-24-30

        $cacheKey = $keyword.'_'.$offset;
        $cache = Cache::tags('lts');
        $res = $cache->get($cacheKey);
        if (! $res) {
            $res = LyLtsController::get($keyword, $offset);
            //添加订阅id
            // if(isset($res['subscribe_id']) && isset( $res['offset']) ){
            //     $res['subscribe_id']  = $res['subscribe_id'] . ',' . $res['offset']; //1-125,24
            // }
            $cache->add($cacheKey, $res, now()->addMinutes(720));
            Log::notice(__CLASS__, ['Cached item', $cacheKey]);
        }

        return $res;
    }
}
