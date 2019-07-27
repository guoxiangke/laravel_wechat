<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/3
 * Time: 上午9:46.
 */

namespace App\Services\Wechat\Resources;

use App\Models\LyMeta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\LyMetaController;

class LyHandle
{
    public static function process($keyword, $isLyApp = false)
    {
        $oriKeyword = $keyword;
        //3位数关键字xxx
        $keyword = substr($oriKeyword, 0, 3);
        $offset = substr($oriKeyword, 3) ?: 0;
        //TODO 历史节目1-3-7 权限控制
        $itemKey = 'index';
        if ($isLyApp) {
            $itemKey = 'ly_index';
        }

        //601-635 or 102-201-901
        $lyMetas = LyMeta::whereNull('stop_play_at')->orderBy('category')->get()->toArray();
        // region 600
        if ($keyword == 600) {
            $tmp_category = '';
            $menu_text = "回复【】内编号获取相应资源\n=====".LyMeta::CATEGORY[0].'====='.PHP_EOL;
            foreach ($lyMetas as $key => $lyMeta) {
                if ($lyMeta['category'] != $tmp_category) {
                    $categoryName = LyMeta::CATEGORY[$lyMeta['category']];
                    $menu_text .= "====={$categoryName}=====".PHP_EOL;
                    $tmp_category = $lyMeta['category'];
                }
                $menu_text .= "【{$lyMeta['index']}】{$lyMeta['name']}".PHP_EOL;
            }
            $moreLyLink = route('lymeta.index');

            return [
                'type'=>'text',
                'ga_data'       => [
                    'category' => 'lyapi_menu',
                    'action'   => '600',
                ],
                'offset'   => $offset,
                'content'=> $menu_text."回复【】内编号获取相应资源\n不带【中括号】\n<a href='{$moreLyLink}'>更多节目介绍请点击</a>",
            ];
        }
        // endregion
        $keyArray = array_pluck($lyMetas, $itemKey);
        $code = null;
        $key = array_search($keyword, $keyArray);
        $LyMeta = $lyMetas[$key];
        $code = $LyMeta['code'];
        //region
        if (! $code) {
            return;
        }

        //todo return json for api.
        if (in_array($keyword, $keyArray)) {
            $cache = Cache::tags('lyaudio');
            $cacheKey = $keyword.'_'.$offset.($isLyApp ? '' : '_ly');
            $res = $cache->get($cacheKey);
            if (! $res) {
                $res = LyMetaController::get($code, $offset);
                $cache->put($cacheKey, $res, now()->addMinutes(720));
                Log::notice(__CLASS__, ['Cached LYitem', $cacheKey]);
            }
            // 不要620图文
            if ($isLyApp && $keyword == 601) {
                unset($res['custom_res']);
            }

            return $res;
        }
        //endregion
    }
}
