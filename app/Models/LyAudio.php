<?php

namespace App\Models;

use App\Services\Upyun;
use App\Traits\HasMorphsTargetField;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Actuallymab\LaravelComment\Commentable;
use Illuminate\Support\Facades\URL;
use Rinvex\Categories\Traits\Categorizable;

class LyAudio extends Model
{
    protected $table = 'ly_audios';
    use SoftDeletes;
    use HasMorphsTargetField;

    //oneAlbum instead of album() form HasMorphsTargetField
    public function oneAlbum()
    {
        return $this->hasOne(Album::class, 'id', 'album_id');
    }

    protected $dates = ['deleted_at'];

    use Categorizable; //专辑分类
    // use Commentable; //可以对该天节目的评论，如果没有则创建！
    protected $mustBeApproved = true;
    protected $fillable = ['target_type', 'target_id', 'play_at', 'excerpt', 'wave', 'album_id'];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'commentable_id', 'id');
    }

    //180101
    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('ymd');
    }

    public function getMusicUrl()
    {
        if ($this->target_type == LyMeta::class) {
            $date = $this->play_at;
            $code = $this->lymeta->code;
            $year = '20'.substr($date, 0, 2);
            $tmp_path = $year.'/'.$code.'/'.$code;
            $path = $tmp_path.$date.'.mp3';
            $audioUrl = Upyun::ONE_DOMAIN.'/share/lytx/'.$path;
        //https://oneybzx.yongbuzhixi.com/share/lytx/2018/bf/bf180303.mp3
        } else { //LyLts::class
        }

        return $audioUrl;
    }

    public function getUrl()
    {
        $class = (new \ReflectionClass($this))->getShortName();

        return URL::route("{$class}.show", ['slug'=>$this->slug]);
    }

    public function getImageUrl()
    {
        $image = false;
        //从XX/所属专辑中找图片
        if ($this->target_type == LyMeta::class) {
            $lyMeta = $this->lymeta()->first();
            if ($lyMeta) {
                $image = $lyMeta->image;
            }
        } else {//LyLts::class
            $lyLts = $this->lylts()->first();
            if ($lyLts) {
                $image = $lyLts->image;
            }
        }

        if (!$image && $this->album_id) {
            $album = $this->album()->first();
            $image = config('app.url').'/storage/'.$album->image;
        }
        if (!$image) {
            $image = URL::asset('public/images/ybzx01.jpg');
        }

        return $image;
    }

    //true order
    public function getIndex()
    {
        if ($this->album_id) {
            return self::where('album_id', $this->album_id)
          ->where('play_at', '<=', $this->play_at)
          ->count();
        } else {
            return false;
        }
    }

    public function toWechat()
    {
        $description = $this->excerpt;
        if ($this->target_type == LyMeta::class) {
            $title = '【'.$this->lymeta->index.'】'.$this->lymeta->name;
        }
        if ($this->target_type == LyLts::class) {
            $title = '【'.$this->lylts->index.'】'.$this->lylts->name;
        }

        if ($this->album_id) {
            $album = Album::find($this->album_id);
            $title = $album->title;
            $title .= '('.$this->getIndex().'/'.$album->getPostCounts().')';
        }

        $audioUrl = $this->getMusicUrl();
        $customRes = false;
        if ($this->body) {
            $url = $this->getUrl();
            $image = $this->getImageUrl();
            $customRes = [
              'type'         => 'news',
              'comment_id'   => $this->id,
              'comment_type' => self::class,
              'content'      => [
                'title'       => $title,
                'description' => $description,
                'url'         => $url,
                'image'       => $image,
              ],
              'ga_data' => [
                'category' => 'album_lyaudio',
                'action'   => $this->album_id.'_'.$this->id,
              ],
            ];
        }

        $res = [
          'type'          => 'music',
          'custom_res'    => $customRes,
          'comment_id'    => $this->id,
          'ga_data'       => [
              'category' => 'album_lyaudio',
              'action'   => $this->album_id.'_'.$this->id,
          ],
          'content'=> [
              'title'          => $title,
              'description'    => $description,
              'url'            => $audioUrl,
              'hq_url'         => $audioUrl,
              'thumb_media_id' => null,
          ],
        ];

        return $res;
    }
}
