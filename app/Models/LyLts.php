<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LyLts extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = ['name', 'description', 'author', 'code', 'count', 'index', 'category', 'weight', 'image'];
    public const ModelName = '良院课程';

    //ori: https://s3-ap-northeast-1.amazonaws.com/lyfiles/lts/本科课程/圣经背景/mavbi008.mp3
    //ori: https://lyfiles.s3-ap-northeast-1.amazonaws.com/lts/本科课程/圣经背景/mavbi008.mp3 ->
    //upyun: http://upcdn.yongbuzhixi.com/lts/本科课程/圣经背景/mavbi008.mp3?upt....
    //ltswx2018.up.yongbuzhixi.com
    //last: https://ltswx2018.yongbuzhixi.com/ly/addfiles/lts/本科课程/圣经背景/mavbi008.mp3
    // const CDN = 'https://ltswx2018.yongbuzhixi.com';
    const CDN = 'http://txly2.net';

    const CDN_PREFIX = '/ly/addfiles/lts/';

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    const CATEGORY = [
      0=>'基础课程',
      1=>'本科课程',
      2=>'进深课程',
      3=>'专辑课程',
    ];

    protected $attributes = [
      'weight' => 0,
    ];
}
