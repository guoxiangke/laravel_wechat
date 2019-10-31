<?php
/**
 * Created by PhpStorm.
 * User: dale
 * Date: 2018/9/3
 * Time: 上午9:59.
 */

namespace App\Services;

use App\Models\User;
use App\Models\LyMeta;
use App\Models\WechatAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Api\LyMetaController;

class Helper
{
    public static function dev()
    {
        echo '<pre>';
        // dd('123');
        dd(\App\Services\Wechat::setMenu(1));
    }

    public static function dev_notice($message, $openId = 'oTjEws-8eAAUqgR4q_ns7pbd0zN8')
    {
        //oTjEws-8eAAUqgR4q_ns7pbd0zN8 小永
        //oTjEwsycJgEpiKTzzisTRa8RP8y4 代理群主
        $app = Wechat::init(1);
        $now = Carbon::now();
        $message = Config::get('app.env')."\n".$message."\n".$now;
        $app->customer_service->message($message)->to($openId)->send();
    }

    public static function send_custom_music()
    {
        $mediaId = '0EiLlKqUqHoIZkYcahv0y0-L-gG7i2jJfmCL0OvqHC4';
        $openid = 'oTjEws-8eAAUqgR4q_ns7pbd0zN8';
        $openid = Wechat::TEST_OPEN_ID;
        //$url = 'http://mp3.jdjys.net:81/mp3/4%E8%8B%B1%E6%96%87%E8%B5%9E%E7%BE%8E%E8%AF%97%E6%AD%8C/02/Track01.mp3';
        $url = 'http://edge.flowplayer.org/fake_empire/0/pl.m3u8?1.mp3';
        $url = 'http://flowplayer.cdn.bcebos.com/test/6363.m3u8?1.mp3';
        $path = '/voice/10月3日 下午4点17分.mp3';
        $url = 'http://upcdn.yongbuzhixi.com'.$path.Upyun::sign($path, 21600);
        $url = 'http://upcdnlts.yongbuzhixi.com/?/lts/new/%E4%B8%93%E8%BE%91%E8%AF%BE%E7%A8%8B/35%E5%91%A8%E5%B9%B4%E9%99%A2%E5%BA%86%E5%9F%B9%E7%81%B5%E4%BC%9A/mavsp401.mp3';
        $url = 'http://upcdnlts.yongbuzhixi.com/?/lts/new/%E6%9C%AC%E7%A7%91%E8%AF%BE%E7%A8%8B/%E4%BF%9D%E7%BD%97%E4%B9%A6%E4%BF%A1I/mavpa001.mp3';
        $music = [
            'title'          => '51oneindex2',
            'description'    => '点击▶️收听',
            'url'            => $url,
            'hq_url'         => $url,
            'thumb_media_id' => $mediaId,
        ];
        $app = Wechat::init(1);
        // $music = LyMetaController::get('se')['content'];
        // $music['thumb_media_id'] = $mediaId;
        $message = new  \EasyWeChat\Kernel\Messages\Music($music);
        $re = $app->customer_service->message($message)->to($openid)->send();
        // $u = \App\Models\WechatUserProfile::pluck('openid')->all();
        // foreach ($u as $key => $openid) {

        // }
        dd('done');
        // dd($re);
        // dd($app->material->get($mediaId));
        // dd($app->material->stats());
    }

    public static function point()
    {
        $user = User::first();
        $amount = 10; // (Double) Can be a negative value
        $message = 'The reason for this transaction';

        //Optional (if you modify the point_transaction table)
        // $data = [
        //     'ref_id' => 'someReferId',
        // ];
        $data = [];

        $transaction = $user->addPoints($amount, $message, $data);

        dd($transaction);
    }

    public static function updateMenu()
    {
        $uid = 4;
        $account = WechatAccount::find($uid);
        $app = Wechat::init($account);
        // $list = $app->menu->list();
        // $list = $app->base->getValidIps();
        $current = $app->menu->current();
        $buttons = $current['selfmenu_info']['button'];
        $buttons[0]['sub_button']['list'][3]['url'] = 'https://r.729ly.net/platforms';
        foreach ($buttons as $key => $button) {
            $buttons[$key]['sub_button'] = $buttons[$key]['sub_button']['list'];
        }
        // $app->menu->delete();
        // $app->menu->create($buttons);
        $current = $app->menu->current();
        dd($current, $buttons);
        // $current = $app->menu->current();
    }

    /**
     * [sendRedPack 红包积分发送].
     *
     * @return [type] [description]
     */
    public static function sendRedPack()
    {
        $recommenderIds = DB::table('users')->select(DB::raw('count(*) as count, user_id'))->where('subscribe', 1)->groupBy('user_id')->orderBy('count', 'desc')->limit(100)->get()->sortByDesc('count')->filter(function ($value, $key) {
            return $value->count >= 50;
        });

        foreach ($recommenderIds as $recommender) {
            if (! in_array($recommender->user_id, [1, 3, 687])) {
                $user = \App\Models\User::find($recommender->user_id);
                $openId = $user->name;
                $points = $user->currentPoints();
                echo '==================='.PHP_EOL;
                echo $user->profile->nickname.'--';
                echo $recommender->user_id.PHP_EOL;
                echo $recommender->count.PHP_EOL;
                echo $points.PHP_EOL;
                if ($points / 10000 > 5) {
                    //5万积分=50元
                    $result = false;
                    // $result = $user->addPoints(-50000, '红包发送');
                    \Log::error('红包积分变动', [$result]);
                    $amount = 5000; //100=1元
                    // $result = Wechat::sendRedpack($amount, $openId);
                    \Log::error('红包发送', [$result]);
                    //发送通知:
                    $res['type'] = 'text';
                    $res['content'] = "您好,感谢您对[永不止息-云彩助手]的宣传,请查收红包!\n这是小永的一点心意,祝您新年快乐,主内平安!";
                    Wechat::send($res, $openId);
                }
            }
        }
    }

    public static function trimAlbumTitle($albumId = 49)
    {
        $posts = Post::where('target_id', $albumId)->get();
        foreach ($posts as $post) {
            echo $post->title.PHP_EOL;
            $post->title = trim($post->title);
            // echo $post->title.PHP_EOL;
            $post->save();
        }
    }

    public static function genByYear($year = 2019)
    {
        $begin = Carbon::parse('first day of January')->timestamp;
        for ($i = 0; $i < 365; $i++) {
            $now = $begin + 86400 * $i;
            $monthDay = date('md', $now);
            $order = date('ymd', $now);
            // echo $monthDay . ': ' . $order . PHP_EOL;

            $categoryId = 103;
            $AlbumId = 68;
            $mp3Url = LyMeta::CDN_WEB.LyMeta::CDN_PREFIX."2019/devotionals-psalm/devotionals-psalm{$order}.mp3";
            // https://lywx2018.yongbuzhixi.com/ly/audio/
            //https://txly2.net/ly/audio/2019/devotionals-psalm/devotionals-psalm190202.mp3
            $post = \App\Models\Post::firstOrcreate([
                'author_id'  => 1,
                'title'      => $monthDay,
                'excerpt'    => '',
                'body'       => '',
                'status'     => 'PUBLISHED',
                'category_id'=> $categoryId,
                'target_type'=> 'App\\Models\\Album',
                'target_id'  => $AlbumId,
                'order'      => $order,
                'mp3_url'    => $mp3Url,
            ]);
            $path = storage_path("app/public/albums_{$AlbumId}.txt");
            file_put_contents($path, $post->id.';'.$order.';'.$post->slug.PHP_EOL, FILE_APPEND);
        }
    }

    public static function getXmla($albumID = 14966661, $sort = 0)
    {
        echo '<pre>';
        $categoryId = 106;
        $AlbumId = 71;

        for ($i = 1; $i < 100; $i++) {
            $url = "http://www.ximalaya.com/revision/play/album?albumId={$albumID}&pageNum={$i}&sort={$sort}&pageSize=30";
            $content = file_get_contents($url);
            $res = json_decode($content, 1);
            $tracks = $res['data']['tracksAudioPlay'];
            if (count($tracks) == 0) {
                break;
            }
            // $path = storage_path("app/public/ximalaya/{$albumID}_{$i}.json");
            // file_put_contents($path, file_get_contents($url));
            foreach ($tracks as $item) {
                $title = $item['trackName'];
                $index = $item['index'];
                $src = $item['src'];
                // $duration = $item['duration'];
                // echo "wget {$src}" . PHP_EOL;
                $mp3Url = '1path:'."/share/resources/ximalaya/{$albumID}/".basename($src);

                $post = \App\Models\Post::firstOrcreate([
                    'author_id'  => 1,
                    'title'      => $title,
                    'excerpt'    => '',
                    'body'       => '',
                    'status'     => 'PUBLISHED',
                    'category_id'=> $categoryId,
                    'target_type'=> 'App\\Models\\Album',
                    'target_id'  => $AlbumId,
                    'order'      => $index,
                    'mp3_url'    => $mp3Url,
                ]);
            }
            // dd($data);
        }
    }

    public static function genByTitle()
    {
        echo '<pre>';
        $categoryId = 105;
        $AlbumId = 70;
        $titles = '';
        $titles = explode(PHP_EOL, $titles);
        foreach ($titles as $key => $title) {
            $title = trim($title);
            $index = $key + 1;
            $path = str_replace('’', '-', $title); //.urlencode()
            $path = str_replace("'", '-', $title); //.urlencode()
            $mp4Url = "/share/albums/70/{$path}"; //'1path:' .
            $title = str_replace('_1080p', '', $title);
            $title = str_replace('.mp4', '', $title);
            // echo $title .'    '. $mp4Url .PHP_EOL;
            $post = \App\Models\Post::firstOrcreate([
                    'author_id'   => 1,
                    'title'       => $title,
                    'excerpt'     => '',
                    'body'        => '',
                    'status'      => 'PUBLISHED',
                    'category_id' => $categoryId,
                    'target_type' => 'App\\Models\\Album',
                    'target_id'   => $AlbumId,
                    'order'       => $index,
                    'mp4_one_path'=> $mp4Url,
                ]);
        }
    }

    public static function setMenu()
    {
        $uid = 1;
        // $uid = 4;
        $menu = [
              [
                'type' => 'click',
                'name' => '一键续订',
                'key'  => '续订',
              ],
              [
                'type' => 'view',
                'name' => '常见问题',
                'url'  => 'https://wechat.yongbuzhixi.com/docs?from=yczsmenu',
              ],
              [
                'type' => 'view',
                'name' => '如何订阅',
                'url'  => 'https://ybzx2018.yongbuzhixi.com/videos/2019/faq/订阅功能好处.mp4?from=yczsmenu',
              ],

        ];

        return Wechat::setMenu($uid, $menu);
    }
}
