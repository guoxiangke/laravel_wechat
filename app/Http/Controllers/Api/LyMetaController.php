<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/7/15
 * Time: 下午9:38.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LyAudio;
use App\Models\LyMeta;
use App\Services\Upyun;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LyMetaController extends Controller
{
    //cache clear
    public function cc()
    {
        return [
            'success' => Cache::tags('lyaudio')->flush(),
            'result'  => 'Cache cleaned for lyaudio!',
        ];
    }

    public function index()
    {
        $lyMetas = LyMeta::active()->orderBy('category')->get();

        return view('lymeta.index', [
            'lymetas'   => $lyMetas,
            'categorys' => LyMeta::CATEGORY,
            // 'shareData' => $shareData,
            // 'signPackage' => $signPackage
        ]);
    }

    public function all()
    {
        return LyMeta::active()->pluck('code');
    }

    /**
     * @param $code
     * @param int $offset
     *
     * @return array|string
     */
    public static function get($code, $offset = 0)
    {
        $customRes = false;
        $cdnLink = LyMeta::CDN;

        $default_desc = '点击▶️收听';
        $customMessage = false;
        $lyMeta = $code;
        if (is_string($lyMeta)) {
            $lyMeta = LyMeta::where('code', $code)->firstOrFail();
        }
        $title = $lyMeta->name;
        $index = $lyMeta->index;
        //dynamic get from http://txly2.net/ 
        // https://729lyprog.net/cc
        if ($index >= 641 && $index <= 645) {
            $ltsUrl = 'https://729lyprog.net';
            $programUrl = $ltsUrl.'/'.$code;
            $html = file_get_contents($programUrl);
            preg_match_all('/((?<=setup\(\{)|(?<=,\{))[^}]*(?=\})/', $html, $matches);
            //TODO 10天内节目
            if (!isset($matches[0]) || count($matches[0]) < 15) {
                Log::error(__CLASS__, [__FUNCTION__, __LINE__, '没有抓去到lts这么多节目', $index, $code, $matches[0]]);

                return false;
            }
            if ($offset > 20) {
                return [
                    'type'          => 'text',
                    'ga_data'       => [
                        'category' => 'lyapi_error',
                        'action'   => '您无权限,超出范围',
                    ],
                    'offset'   => $offset,
                    'content'  => '您无权限,超出范围',
                ];
            }
            $mp3Files = [];
            $titles = [];
            array_map(
                function ($str) use (&$mp3Files, &$titles) {
                    preg_match('/(?<=:\')[^\']*(?=\')/', $str, $matches);
                    if (is_array($matches) && isset($matches[0])) {
                        $mp3Files[] = $matches[0];
                    }
                    preg_match('/(?<=title:\')[^\']*(?=\')/', $str, $matches);
                    if (is_array($matches) && isset($matches[0])) {
                        $titles[] = $matches[0];
                    }
                },
                $matches[0]
            );

            // https://729lyprog.net/ly/audio/mavtm/mavtm013.mp3
            // https://lywx2018.yongbuzhixi.com/ly/audio/2021/mw/mw210605.mp3
            $hqUrl = 'https://lywx2018.yongbuzhixi.com'.$mp3Files[$offset];

            return [
                'type'          => 'music',
                'comment_id'    => 0,
                'subscribe_id'  => $lyMeta->id,
                'ga_data'       => [
                    'category' => 'lyapi_audio',
                    'action'   => $title,
                ],
                'offset'         => $offset,
                // 'custom_message' => $descriptions[$offset],
                'content'        => [
                    'title'          => $titles[$offset].' '.str_replace('良友圣经学院', '', $title),
                    'description'    => $default_desc,
                    'url'            => $hqUrl,
                    'hq_url'         => $hqUrl,
                    'thumb_media_id' => null,
                ],
            ];
        }
        $has_program = false;
        $tmp_offset = 0;

        $time = time();
        do {
            $tmp_time = $time - $offset * 86400;
            $date = date('ymd', $tmp_time); //161129
            $titleDate = date('n/j', $tmp_time); //161129
            $tmp_path = LyMeta::CDN_PREFIX.date('Y', $tmp_time).'/'.$code.'/'.$code;
            $commentDate = $date; //rt,161127
            $path = $tmp_path.$date.'.mp3'; // /2016/rt/rt161127.mp3
            $uri = $cdnLink.$path;
            $musicUrl = $uri; //. Upyun::sign($path);
            $temp = @fast_headers($musicUrl, 1)
                or die("Unable to connect to $musicUrl");
            if ($temp[0] == 'HTTP/1.1 200 OK') {//远程有!!!
                $has_program = true;
                //bug no news for mw
                $lyAudio = LyAudio::where('play_at', (int) $date)
                    ->where('target_id', $lyMeta->id)
                    ->first();
                if ($lyAudio) {
                    if ($lyAudio->excerpt) {
                        $customMessage = $lyAudio['excerpt'];
                    }
                    if ($lyAudio->body) {
                        $customRes = [
                            'type'    => 'news',
                            'content' => [
                                'title'       => $title.' '.$titleDate,
                                'description' => $lyAudio->excerpt,
                                'url'         => route('LyAudio.show', ['slug'=>$lyAudio->slug]),
                                'image'       => $lyMeta->image,
                            ],
                            'ga_data' => [
                                'category' => 'lymeta_news',
                                'action'   => $lyMeta->name,
                            ],
                        ];
                        // dd($tplMessage);
                    }
                }
                break;
            } else {
                $offset++;
                $tmp_offset++;
            }
        } while (!$has_program && $tmp_offset < 7); //上下范围7天

        if (!$has_program) {
            return [
                 'type'          => 'text',
                 'ga_data'       => [
                     'category' => 'lyapi_error',
                     'action'   => '上下范围7天内无节目',
                 ],
                 'offset'   => $offset,
                 'content'  => '上下范围7天内无节目',
             ];
        }

        if ($offset) {
            $title .= ' '.$titleDate;
        }
        // target_type = lymeta
        // target_id = 161127
        $commentDate = $lyMeta->id.','.$commentDate;

        return [
            'type'          => 'music',
            'comment_id'    => $commentDate,
            'subscribe_id'  => $lyMeta->id,
            'ga_data'       => [
                'category' => 'lyapi_audio',
                'action'   => $lyMeta->name,
            ],
            'custom_res'     => $customRes,
            'custom_message' => $customMessage,
            'offset'         => $offset,
            'content'        => [
                'title'          => $title,
                'description'    => $default_desc,
                'url'            => $musicUrl,
                'hq_url'         => $musicUrl,
                'thumb_media_id' => null,
            ],
        ];
    }
}
