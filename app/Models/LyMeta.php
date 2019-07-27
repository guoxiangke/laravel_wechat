<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LyMeta extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = ['name', 'description', 'category', 'index', 'ly_index', 'code', 'day', 'stop_play_at', 'image', 'author'];

    public const ModelName = '良友知音';
    //ori: http://txly2.net/ly/audio/2018/ee/ee181023.mp3 ->
    //upyun: http://lytx2.yongbuzhixi.com/ly/audio/2018/ee/ee181023.mp3?upt.....
    //last: http://lywx2018.yongbuzhixi.com/ly/audio/2018/ee/ee181023.mp3
    const CDN = 'http://lywx2018.yongbuzhixi.com';
    const CDN_WEB = 'https://lywx2018.yongbuzhixi.com';
    const CDN_PREFIX = '/ly/audio/';

    protected $casts = [
        'stop_play_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function album()
    {
        return $this->hasMany(Album::class);
    }
    /**
     * Scope a query to only include active.
     *
     * @param  $query  \Illuminate\Database\Eloquent\Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('stop_play_at');
    }
    public static $slugs = [
        [
            'name' => '生活智慧',
            'slug' => 'Wisdom',
        ],
        [
            'name' => '关怀辅导',
            'slug' => 'Care',
        ],
        [
            'name' => '婚恋家庭',
            'slug' => 'Family',
        ],
        [
            'name' => '诗歌音乐',
            'slug' => 'Song',
        ],
        [
            'name' => '生命成长',
            'slug' => 'Grow',
        ],
        [
            'name' => '圣经讲解',
            'slug' => 'Bible',
        ],
        [
            'name' => '课程训练',
            'slug' => 'Course',
        ],
        [
            'name' => '少数民族',
            'slug' => 'Minority',
        ],
    ];
    //0-7
    const CATEGORY = [
        '生活智慧',
        '关怀辅导',
        '婚恋家庭',
        '诗歌音乐',
        '生命成长',
        '圣经讲解',
        '课程训练',
        '少数民族',
    ];
}
