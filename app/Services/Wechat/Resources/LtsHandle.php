<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/29
 * Time: 下午10:21.
 */

namespace App\Services\Wechat\Resources;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\LyLtsController;

class LtsHandle
{
    public static function process($oriKeyword)
    {
        $keyword = substr($oriKeyword, 0, 3);
        // #108 == #1080 bug！
        // $cacheKey = $keyword.'_'.$offset;
        $cacheKey = $keyword;

        //给$offset设置默认值
        if (strlen($oriKeyword) > 3) {
            //1-24-30
            $offset = substr($oriKeyword, 3);
            $cacheKey = $cacheKey.'_'.$offset;
        } else {
            $offset = 0;
        }

        $cache = Cache::tags('lts');
        $res = $cache->get($cacheKey);
        if (! $res) {
            $res = LyLtsController::get($keyword, $offset);
            //添加订阅id
            // if(isset($res['subscribe_id']) && isset( $res['offset']) ){
            //     $res['subscribe_id']  = $res['subscribe_id'] . ',' . $res['offset']; //1-125,24
            // }
            $now = Carbon::now();
            $ttl = $now->diffInMinutes($now->copy()->endOfDay());
            $cache->add($cacheKey, $res, now()->addMinutes($ttl));
            Log::notice(__CLASS__, ['Cached item', $cacheKey]);
        }

        return $res;
    }
}
