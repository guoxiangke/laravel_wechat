<?php

namespace App\Models;

use Overtrue\Pinyin\Pinyin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    const MAX_INDEX = 700; //701-799
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $casts = [
        'expire_at' => 'datetime',
    ];
    //订阅封面
    protected $fillable = [
      'title',
      'excerpt',
      'body',
      'price',
      'ori_price',
      'expire_at',
      'category_id',
      'count',
      'lymeta_id',
      'audio_only',
      'active',
      'url',
      'image',
      'rrule',
    ];
    public const ModelName = '自建专辑';
    protected $attributes = [
      'price'     => 1900,
      'ori_price' => 5900,
      'count'     => 0,
    ];

    public function getPriceAttribute($value)
    {
        return $value / 100;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public function getOriPriceAttribute($value)
    {
        return $value / 100;
    }

    public function setOriPriceAttribute($value)
    {
        $this->attributes['ori_price'] = $value * 100;
    }

    public function setCategoryIdAttribute($category_id)
    {
        $category = app('rinvex.categories.category');
        //1.如果创建时,专辑留空,则创建一个root分类
        $currentCategory = $category->withDepth()->find($category_id);
        //2.如果选择的是一个root分类,则创建同名其子分类. 并attach
        $depth = $currentCategory->depth;
        if ($depth == 0) {// root
            $name = $this->attributes['title'];
            $pinyin = new Pinyin(); // 默认
            $pinyins = $pinyin->convert($name);
            $slug = str_slug(implode('-', $pinyins));
            $newCategory = $category->create([
          'name'       => $name,
          'description'=> $name,
          'slug'       => $slug,
          'parent_id'  => $category_id,
        ]);
            $category_id = $newCategory->id;
        }
        $this->attributes['category_id'] = $category_id;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function save(array $options = [])
    {
        // If no author has been assigned, assign the current user's id as the author of the post
        if (! $this->user_id && Auth::user()) {
            $this->user_id = Auth::user()->id;
        }
        if (! $this->modified_id && Auth::user()) {
            $this->modified_id = Auth::user()->id;
        }

        parent::save();
    }

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

    public function posts()
    {
        if ($this->lymeta_id) {
            return $this->hasMany(LyAudio::class, 'album_id', 'id');
        } else {
            return $this->hasMany(Post::class, 'target_id', 'id');
        }
    }

    public function toWechat()
    {
        $album = $this;
        $subscribeType = self::class;
        $subscribeId = $this->id;
        $commentId = null;
        $excerpt = ! empty($album->excerpt) ? $album->excerpt : null;
        if (! $excerpt) {
            $excerpt = str_limit(strip_tags($album->body), '120');
        }
        $image = config('app.url').'/storage/'.$album->image;

        $firstPostUrl = false;
        $description = $excerpt;

        $customRes = false;

        //region LyAudio Album.
        if ($this->lymeta_id) {
            $commentType = LyAudio::class;
            $firstPost = LyAudio::where('album_id', $this->id)->orderBy('play_at', 'ASC')->first();
        } else {
            $commentType = Post::class;
            $firstPost = Post::where('target_type', $subscribeType)
          ->where('target_id', $subscribeId)
          ->orderBy('order', 'ASC')
          ->first();
        }
        //endregion

        $total = $this->getPostCounts();
        $title = $this->title.'(1'.'/'.$total.')';

        if ($firstPost) {
            //only get audio!!!
            $customRes = $firstPost->toWechat();
            $class = (new \ReflectionClass($firstPost))->getShortName();
            //LyAudio.show
            //Post.show
            $firstPostUrl = route("{$class}.show", ['slug'=>$firstPost->slug]);
            if (isset($res['custom_res'])) {
                $customRes = $customRes['custom_res'];
            }
            $commentId = $firstPost->id;
            $customRes['content']['title'] = $title;
        } else {
            $description = "专辑未就绪,请勿订阅,敬请期待\n".$description;
        }

        if ($firstPostUrl) {
            $url = $firstPostUrl;
        } else {
            $url = config('app.url').'/focus';
        }

        $res = [
          'type'         => 'news',
          'custom_res'   => $customRes,
          'comment_type' => $commentType,
          'comment_id'   => $commentId,
          'content'      => [
              'title'       => $title,
              'description' => $description,
              'url'         => $url,
              'image'       => $image,
          ],
          'ga_data' => [
              'category' => 'album_news',
              'action'   => $subscribeType.'_'.$subscribeId,
          ],
      ];

        return $res;
    }

    //701-799
    public function getIndex()
    {
        return self::MAX_INDEX + $this->id;
    }

    public function getPostCounts()
    {
        return $this->posts()->count();
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function toogleActive()
    {
        $this->active = ! $this->active;
        $this->save();
    }

    public function toogleAudioOnly()
    {
        $this->audio_only = ! $this->audio_only;
        $this->save();
    }
}
