<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Http\Controllers\Controller;
use App\Models\LyAudio;
use App\Models\LyMeta;
use App\Models\User;
use App\Models\WechatProfile;
use App\Services\Upyun;
use Illuminate\Support\Facades\Request;
use App\Services\Wechat;

class LyAudioController extends Controller
{
    public function showSlug($slugString)
    {
        $post = LyAudio::whereSlug($slugString)->firstOrFail();
        return $this->show($post);
    }

    public function show(LyAudio $audio)//$id
    {

        $lyMeta = $audio->lymeta()->first();
        $imgUrl = Upyun::IMAGE_CND_PREFIX . $lyMeta->image;
        $title = "【{$lyMeta->index}】{$lyMeta->name}-{$audio->play_at}";
        if($audio->target_type == LyMeta::class){
            //30天之内的链接,使用 LyMeta::CDN
            $playAt = \DateTime::createFromFormat('ymd', $audio->play_at);
            $from =  strtotime("-20 day");
            if($playAt->format('U') >= $from){
                $audioUrl = LyMeta::CDN_WEB . LyMeta::CDN_PREFIX . date('Y').'/'. $lyMeta->code . '/' . $lyMeta->code . $audio->play_at . '.mp3';
            }else{
                $audioUrl = Upyun::ONE_DOMAIN . '/share/lytx/' . $playAt->format('Y') .'/'. $lyMeta->code . '/' . $lyMeta->code . $audio->play_at . '.mp3';
            }
        }else{
            $audioUrl  = false;
            $index = $lyMeta->index;
            $offset = $audio->play_at;
            $res = LyLtsController::get($index, $offset);
            if(isset($res['content']['hq_url'])){
                $audioUrl  = $res['content']['hq_url'];
            }

        }

        // $link = Request::fullUrl();
        $link = str_replace('http://', 'https://', url()->full());
        $signPackage = Wechat::getSignPackage($link);
        $title = "【{$lyMeta->index}】{$lyMeta->name}-{$audio->play_at}";
        $shareData = [
            'title' =>  $title,
            'link' => $link,
            'imgUrl' => $imgUrl,
        ];
        return view('lyaudios.show', compact('audio', 'title','audioUrl','shareData','signPackage'));
    }
}
