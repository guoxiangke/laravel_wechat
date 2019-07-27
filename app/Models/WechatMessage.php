<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WechatMessage extends Model
{
    protected $table = 'wechat_messages';
    protected $fillable = ['msg_id', 'to_user_name', 'msg_type', 'from_user_name', 'create_time', 'content'];
}
