<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class WechatUserProfile extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = 'wechat_user_profiles';
    protected $fillable = ['user_id', 'subscribe', 'openid', 'nickname', 'sex', 'language', 'city', 'province', 'country', 'headimgurl', 'subscribe_time', 'unionid', 'remark', 'groupid', 'subscribe_scene', 'qr_scene', 'qr_scene_str'];

    // public $incrementing = false;
    // public $primaryKey = 'openid';

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
