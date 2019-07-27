<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatAccount extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = 'wechat_accounts';
    protected $fillable = ['name','to_user_name','app_id','secret','token','aes_key','is_certified','menu','resources','description','image_qr'];

    // todo 转换成数组
    protected $casts = [
        'menu' => 'array',
        'resources' => 'array',
    ];

    //1个微信账户 有多个 自动回复规则
    public function replies()
    {
        return $this->hasMany(WechatAutoReply::class, 'to_user_name', 'to_user_name');
    }
}
