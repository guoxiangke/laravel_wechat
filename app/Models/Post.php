<?php

namespace App\Models;

use App\Services\Upyun;
use App\Traits\HasMorphsTargetField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//https://github.com/spatie/laravel-tags/issues/134 hack core!!!
// vi vonder/spatie/laravel-tags/src/Tag.php   line 66
// use Spatie\MediaLibrary\HasMedia\HasMedia;
// use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
// use Rinvex\Categories\Traits\Categorizable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Spatie\Tags\HasTags;

// use Actuallymab\LaravelComment\Commentable;

class Post extends Model // implements HasMedia
{
    public const ModelName = '文章类型';
    use SoftDeletes;
    use HasTags;
    use HasMorphsTargetField; //album
    // use Categorizable;
    // use Commentable;
    protected $mustBeApproved = true;

    protected $fillable = [
      'title',
      // 'slug',
      'seo_title',
      'excerpt',
      'body',
      'meta_description',
      'meta_keywords',
      'status',
      'user_id',
      'author_id',
      'modified_id',
      'category_id',
      'order',
      'target_type',
      'target_id',
      'mp3_url',
      'mp4_url',
      'image_url',
      'youtube_vid',
      'origin_url',
      'pan_url',
      'pan_password',
      'mp4_one_path',
      'mp4_upyun_path',
      'qq_vid',
    ];
    // public $translatable = ['en'];

    protected $attributes = [
      'order'  => 1,
      'status' => 'PUBLISHED',
    ];

    public function save(array $options = [])
    {
        //todo $newsItem->addMedia($pathToFile)->toMediaCollection('images');

        // If no author has been assigned, assign the current user's id as the author of the post
        if (!$this->user_id && Auth::user()) {
            $this->user_id = Auth::id();
        }
        if (!$this->modified_id && Auth::user()) {
            $this->modified_id = Auth::id();
        }
        parent::save();
    }

    //todo protected $translatable = ['title', 'seo_title', 'excerpt', 'body', 'slug', 'meta_description', 'meta_keywords'];

    const PUBLISHED = 'PUBLISHED';
    const DRAFT = 'DRAFT';
    const PENDING = 'PENDING';

    protected $guarded = [];

    //公众号uid  wxid@mp role：mp
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    //创建者uid wxid@wx  role：wx
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //更改者uid wxid@wx role：wx
    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_id', 'id');
    }

    /**
     * Scope a query to only published scopes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished(Builder $query)
    {
        return $query->where('status', '=', static::PUBLISHED);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function albumId()
    {
        $albumId = 'null';
        $album = $this->album()->first();
        if ($album) {
            $albumId = $album->id;
        }

        return $albumId;
    }

    //true order
    public function getIndex()
    {
        $album = $this->album()->first();
        if ($album) {
            $order = self::where('target_type', Album::class)
          ->where('target_id', $album->id)
          ->where('order', '<=', $this->order)
          ->count();

            return $order;
        } else {
            return false;
        }
    }

    public function getImageUrl()
    {
        $image = false;
        //从文章/所属专辑中找图片
        if (!empty($this->image_url)) {
            $image = $this->image_url;
        } elseif ($this->image) {
            $image = config('app.url').'/storage/'.$this->image;
        }
        if (!$image && $this->youtube_vid) {
            $image = "https://img.youtube.com/vi/{$this->youtube_vid}/hqdefault.jpg";
        }
        if (!$image) {
            $album = $this->album()->first();
            if ($album) {
                $image = config('app.url').'/storage/'.$album->image;
            }
        }
        if (!$image) {
            $image = URL::asset('public/images/ybzx01.jpg');
        }

        return $image;
    }

    //get wechat res
    public function toWechat()
    {
        $image = $this->getImageUrl();

        $title = $this->title;
        $excerpt = !empty($this->excerpt) ? $this->excerpt : null;
        if (!$excerpt) {
            $excerpt = str_limit(strip_tags($this->body), '120');
        }
        $album = $this->album()->first();
        $returnCustomRes = false;
        if ($album) {
            $title = $title.'('.$this->getIndex().'/'.$album->getPostCounts().')';
            if ($album->audio_only) {
                $returnCustomRes = true;
            }
        }
        $link = '';
        $link = config('app.url')."/posts/$this->slug";
        $content = "<a href='$link'>$title</a>\n$excerpt\n";

        $res = false;
        $albumId = $this->albumId();
        $customRes = false;
        if ($audio = $this->get_audio()) {
            $hqUrl = $audio['url'];
            $default_desc = '点击▶️收听 公号:云彩助手';
            $customRes = [
          'type'          => 'music',
          'comment_id'    => $this->id,
          'ga_data'       => [
              'category' => 'post_audio',
              'action'   => $this->id,
          ],
          'content'=> [
              'title'          => $title,
              'description'    => $default_desc,
              'url'            => $hqUrl,
              'hq_url'         => $hqUrl,
              'thumb_media_id' => null,
          ],
        ];

            if ($returnCustomRes) {
                return $customRes;
            }
        }
        $url = URL::route('Post.show', ['slug'=>$this->slug]);
        if (!$image) {
            $res = [
          'type'       => 'text',
          'custom_res' => $customRes,
          'comment_id' => $this->id,
          'content'    => $content,
          'ga_data'    => [
            'category' => 'album_post',
            'action'   => $albumId.'_'.$this->id,
          ],
        ];
        } else {
            $res = [
          'type'       => 'news',
          'custom_res' => $customRes,
          'comment_id' => $this->id,
          'content'    => [
            'title'       => $title,
            'description' => $excerpt,
            'url'         => $url,
            'image'       => $image,
          ],
          'ga_data' => [
            'category' => 'album_post',
            'action'   => $albumId.'_'.$this->id,
          ],
        ];
        }

        return $res;
    }

    public function get_audio()
    {
        $audio = false;
        if ($this->mp3) {
            $audio['url'] = $this->mp3;
        }
        if ($this->mp3_url) {
            $audio['url'] = str_replace('1path:', Upyun::ONE_DOMAIN, $this->mp3_url);
            $audio['url'] = str_replace('txly2:', LyMeta::CDN_WEB, $audio['url']);
        }

        return $audio;
    }

    public function get_video()
    {
        $video = false;
        if ($this->mp4_url) {
            // $video['url'] = $this->mp4_url;
            $video['url'] = str_replace('1path:', Upyun::ONE_DOMAIN, $this->mp4_url);
        }

        if ($this->mp4_one_path) {
            $video['url'] = Upyun::ONE_DOMAIN.$this->mp4_one_path;
        }

        $video['vtt']['en'] = false;
        $video['vtt']['cn'] = false;
        $video['crossorigin'] = false;
        if ($this->mp4_upyun_path) {
            if (!isset($video['url'])) {
                $video['url'] = Upyun::DOMAIN.$this->mp4_upyun_path;
            }
            if ($this->youtube_vid) {
                $vttEnUrl = Upyun::ONE_DOMAIN.'/share/resources/srts/'.$this->youtube_vid.'.en.vtt';
                $temp = get_headers($vttEnUrl, 1)
                  or die("Unable to connect to $vttEnUrl");
                if ($temp[0] == 'HTTP/1.1 200 OK') {
                    $video['vtt']['en'] = $vttEnUrl;
                    $video['crossorigin'] = true;
                }

                $vttCnUrl = Upyun::ONE_DOMAIN.'/share/resources/srts/'.$this->youtube_vid.'.cn.vtt';
                $temp = get_headers($vttCnUrl, 1)
                  or die("Unable to connect to $vttCnUrl");
                if ($temp[0] == 'HTTP/1.1 200 OK') {
                    $video['vtt']['cn'] = $vttEnUrl;
                    $video['crossorigin'] = true;
                }
            }
            if ($video['crossorigin']) {
                // $video['url'] = Upyun::DOMAIN . $this->mp4_upyun_path;
            }
        }
        if (isset($video['url'])) {
            $video['image_url'] = Upyun::IMAGE_CND_PREFIX.$this->getImageUrl();
        }

        return $video;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'commentable_id', 'id');
    }
}
