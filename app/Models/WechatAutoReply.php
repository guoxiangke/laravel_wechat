<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatAutoReply extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    //对应的公众号gh_id, -1==ALL
    const ALL_ACCOUNTS = 'gh_all';
    //自动回复类型
    const MESSAGE_TYPE_MAPPING = [
        'Text' => '文本',
        'Image' => '图片',
        'Voice' => '声音',
        'Video' => '视频',
        'News' => '图文',
        'Transfer' => '转接客服',
    ];
    protected $fillable = ['name', 'to_user_name', 'type', 'patten', 'content'];
}
