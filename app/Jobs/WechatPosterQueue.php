<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Album;
use App\Services\Wechat;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WechatPosterQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $openId;
    protected $avatarPath;
    protected $qrImagePath;
    protected $isTemporary;
    protected $album; //专辑推荐码海报
    const CACHE_TAG = 'poster_01'; // date('m'); //poster_01
    protected $cacheDays;
    protected $albumId;
    protected $destImagePath;
    protected $cacheKey;
    protected $keyword;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $isTemporary = true, $albumId = false, $keyword = false)
    {
        $this->user = $user;
        $this->isTemporary = $isTemporary;
        $this->albumId = $albumId;
        $this->cacheDays = $isTemporary ? 30 : 3650;
        $openId = $user->profile->openid;
        $this->openId = $openId;
        $this->avatarPath = storage_path('app/avatars/wechat/'.$openId.'.png');
        if ($isTemporary) {
            if ($albumId) {
                $this->cacheKey = $openId.'_'.$albumId;
                $this->destImagePath = storage_path('app/qrcodes/poster/'.$openId."_{$albumId}.png");
                $this->qrImagePath = storage_path('app/qrcodes/user/tmpAlbum/'.$openId."_{$albumId}.png");
            } else {
                $this->cacheKey = $openId.'_tmp';
                $this->destImagePath = storage_path('app/qrcodes/poster/'.$openId.'.png');
                $this->qrImagePath = storage_path('app/qrcodes/user/tmp/'.$openId.'.png');
            }
        } else {
            $this->cacheKey = $openId.'_forever';
            $this->destImagePath = storage_path('app/qrcodes/forever/'.$openId.'.png');
            $this->qrImagePath = storage_path('app/qrcodes/user/forever/'.$openId.'.png');
        }
        //默认false
        if ($keyword) {
            $this->keyword = $keyword;
            switch ($keyword) {
                case '活动':
                    $this->cacheKey .= '_activity';
                    break;

                case '推荐':
                    $this->cacheKey .= '_recommend';
                    break;

                default:
                    // code...
                    break;
            }
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $app = Wechat::init(1);
        $user = $this->user;
        $openId = $this->openId;

        $res = [
            'type'    => 'text',
            'content' => '出错了',
            'ga_data' => [
                'category' => '_recommend',
                'action'   => $user->id.'_'.$openId,
            ],
        ];

        $isTemporary = $this->isTemporary;

        $cache = Cache::tags(self::CACHE_TAG);
        $cacheKey = $this->cacheKey;
        $mediaId = $cache->get($cacheKey);

        if (! $mediaId) {
            $avatarPath = $this->avatarPath;
            $qrImagePath = $this->qrImagePath;
            $destImagePath = $this->destImagePath;
            //else do blow!
            if (! file_exists($avatarPath) || ! file_exists($qrImagePath)) {
                $this->genQr($app);
            }
            if ($this->keyword == '推荐') {
                $this->genRecommendImage($qrImagePath, $destImagePath);
            } else {
                $this->genImage($qrImagePath, $destImagePath);
            }
            $result = $app->material->uploadImage($destImagePath);
            if (isset($result['media_id'])) {
                $mediaId = $result['media_id'];
                $cache->put($cacheKey, $mediaId, now()->addDays($this->cacheDays));
                unlink($destImagePath);
            } else {
                Log::error(__FILE__, [__LINE__, __FUNCTION__, $result]);
                $res['content'] = '活动火爆,请6秒后再试';
                $app->customer_service
                    ->message(Wechat::replyByType($res['type'], $res['content']))
                    ->to($openId)
                    ->send();

                return false;
            }
        }

        $app->customer_service
            ->message(Wechat::replyByType('image', $mediaId))
            ->to($openId)
            ->send();

        return true;
    }

    protected function genQr($app)
    {
        $avatarPath = $this->avatarPath;
        $qrImagePath = $this->qrImagePath;
        $isTemporary = $this->isTemporary;

        if (! file_exists($avatarPath)) {
            //download user avatars.
            $image = file_get_contents($this->user->profile->headimgurl);
            file_put_contents($avatarPath, $image);
        }
        $scene_str = 'sharefrom_'.$this->user->id;
        if ($this->albumId) {
            // sharefrom_1_45
            $scene_str .= '_'.$this->albumId;
        }
        if ($isTemporary) {
            $result = $app->qrcode->temporary($scene_str, 6 * 24 * 3600);
        } else {
            $result = $app->qrcode->forever($scene_str);
        }
        $size = 500; //todo  500
        QrCode::format('png')
            ->size($size)
            ->margin(0)
            ->merge($avatarPath, .15, true)
            ->generate($result['url'], $qrImagePath);
    }

    protected function genImage($qrImagePath, $dstImagePath)
    {
        $bgImagePath = public_path('images/WechatIMG191.jpeg');
        $albumId = $this->albumId;
        if ($albumId) {
            $bgImagePath = public_path('images/album.jpeg');
            $index = 700 + $albumId;
            $album = Album::find($albumId);
            $title = "【{$index}】{$album->title} 课程";
            $albumImage = storage_path('app/public/'.$album->image);
        }
        $fontPath = public_path('fonts/yahei.ttf');
        $w = 1080;
        $h = 1920;
        $im = imagecreatefromjpeg($bgImagePath);
        // $im = @imagecreate($w, $h)
        //     or die("Cannot Initialize new GD image stream");
        // $background_color = imagecolorallocate($im, 255, 255, 255);
        $red = imagecolorallocate($im, 228, 37, 53);
        // $white = imagecolorallocate($im, 255, 255, 255);
        // $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);

        $lastDayOfThisMonth = new Carbon('last day of this month');
        $lastDayOfThisMonth = $lastDayOfThisMonth->format('Y.m.d');
        if ($albumId) {
            imagettftext($im, 35, 0, 40, 320, $black, $fontPath, $title);
            //水印图像
            $src_im = imagecreatefromjpeg($albumImage);
            $src_im = imagescale($src_im, 960, 610);
            //$src_info = getimagesize($src_im);

            //水印透明度
            $alpha = 100;

            //合并水印图片
            imagecopymerge($im, $src_im, 60, 400, 0, 0, 960, 610, $alpha);

            $text1 = "1. 回复【{$index}】给 永不止息-云彩助手 即可参与本次活动";
            $text2 = '2. 本活动最终解释权归 永不止息 所有';
            $text3 = '3. 截止时间: '.$lastDayOfThisMonth;
            $text4 = '4. 客服微信: love_yongbuzhixi_com';
        } else {
            $text = '永不止息, 感恩有你';
            imagettftext($im, 35, 0, ($w - 400) / 2, $h - 300, $red, $fontPath, $text);

            $text = '扫码或长按识别二维码';
            imagettftext($im, 24, 0, ($w - 310) / 2, $h - 400, $black, $fontPath, $text);

            $text1 = '1. 回复【活动】给 永不止息-云彩助手 即可参与本次活动';
            $text2 = '2. 分享专属海报, 邀请3位新人关注, 即有机会获得本奖品';
            $text3 = '3. 本活动最终解释权归 永不止息 所有';
            $text4 = '4. 截止时间: '.$lastDayOfThisMonth;
        }

        imagettftext($im, 25, 0, 80, $h - 220, $black, $fontPath, $text1);
        imagettftext($im, 25, 0, 80, $h - 165, $black, $fontPath, $text2);
        imagettftext($im, 25, 0, 80, $h - 110, $black, $fontPath, $text3);
        imagettftext($im, 25, 0, 80, $h - 55, $black, $fontPath, $text4);

        //原始图像
        //得到原始图片信息
        // $dst_im = imagecreatefromjpeg($dst);
        // $dst_info = getimagesize($dst);

        //水印图像
        $src_im = imagecreatefrompng($qrImagePath);
        $src_im = imagescale($src_im, 350, 350);
        //$src_info = getimagesize($src_im);

        //水印透明度
        $alpha = 100;

        //合并水印图片
        imagecopymerge($im, $src_im, ($w - 350) / 2, $h - 700, 0, 0, 350, 350, $alpha);

        //输出合并后水印图片
        imagepng($im, $dstImagePath);
        imagedestroy($im);
        imagedestroy($src_im);

        return $dstImagePath;
    }

    //推荐二维码 500 600
    protected function genRecommendImage($qrImagePath, $dstImagePath)
    {
        $bgImagePath = public_path('images/WechatIMG269.jpg');
        // WechatIMG269.jpeg 700
        // $dstImagePath = getcwd().'/'.time().'.png';
        //$qrImagePath = "./qr.png";
        $fontPath = public_path('fonts/yahei.ttf');
        $w = 1080;
        $h = 1920;
        $im = imagecreatefromjpeg($bgImagePath);
        // $im = @imagecreate($w, $h)
        //     or die("Cannot Initialize new GD image stream");
        // $background_color = imagecolorallocate($im, 255, 255, 255);
        $red = imagecolorallocate($im, 228, 37, 53);
        // $white = imagecolorallocate($im, 255, 255, 255);
        // $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);

        //原始图像
        //得到原始图片信息
        // $dst_im = imagecreatefromjpeg($dst);
        // $dst_info = getimagesize($dst);

        //水印图像
        // $src_im = imagecreatetruecolor(430,430);
        $src_im = imagecreatefrompng($qrImagePath);
        $src_im = imagescale($src_im, 430, 430);
        // $src_info = getimagesize($qrImagePath);

        //水印透明度
        $alpha = 100;

        //合并水印图片
        imagecopymerge($im, $src_im, ($w - 752), $h - 1180, 0, 0, 430, 430, $alpha);

        //输出合并后水印图片
        imagepng($im, $dstImagePath);
        imagedestroy($im);
        imagedestroy($src_im);
    }
}
