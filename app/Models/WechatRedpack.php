<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatRedpack extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = ['return_code', 'return_msg', 'result_code', 'err_code', 'err_code_des', 'mch_billno', 'mch_id', 'wxappid', 're_openid', 'total_amount', 'send_listid'];
}
