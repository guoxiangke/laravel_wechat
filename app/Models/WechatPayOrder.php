<?php

namespace App\Models;

use App\Traits\HasMorphsTargetField;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatPayOrder extends Model
{
    use SoftDeletes;
    use HasMorphsTargetField;
    protected $dates = ['deleted_at'];

    protected $fillable = ['user_id', 'target_type', 'target_id', 'body', 'out_trade_no', 'total_fee', 'trade_type', 'openid', 'prepay_id', 'status', 'body'];

    public function getTotalFeeAttribute($value)
    {
        return $value / 100;
    }

    public function setTotalFeeAttribute($value)
    {
        $this->attributes['total_fee'] = $value * 100;
    }

    public function save(array $options = [])
    {
        // If no author has been assigned, assign the current user's id as the author of the post
        if (! $this->user_id && Auth::user()) {
            $this->user_id = Auth::user()->id;
        }

        parent::save();
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * wechatProfile.
     */
    public function profile()
    {
        return $this->hasOne(WechatUserProfile::class, 'user_id', 'user_id');
    }
}
