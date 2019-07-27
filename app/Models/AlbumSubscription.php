<?php

namespace App\Models;

use App\Traits\HasMorphsTargetField;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class AlbumSubscription extends Model
{
    const FREE_COUNT_LIMIT = 1; //最多订阅1个免费专辑
    const FREE_SHTARE_MIN = 3; //免费订阅本专辑,最少推荐新人数量3
    const RANDOM_SEND_AT = [6, 12, 18, 7, 8, 9, 10, 11, 13, 14, 15, 16, 17, 19, 20, 21, 22];

    use SoftDeletes;
    use HasMorphsTargetField;
    protected $dates = ['deleted_at'];

    protected $fillable = [
      'user_id',
      'wechat_account_id',
      'target_type',
      'target_id',
      'price',
      'count',
      'subscribe_rrule',
      'invoice_rrule',
      'last_invoice_at',
      'send_at',
      'rrule',
      'active',
      'wechat_pay_order_id',
    ];

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

    /**
     * WechatAccount.
     */
    public function account()
    {
        //id or album_subscrition_id
        //, 'id', 'wechat_account_id'
        return $this->hasOne(WechatAccount::class, 'id', 'wechat_account_id');
    }

    public function getPriceAttribute($value)
    {
        return $value / 100;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public function order()
    {
        return $this->hasOne(WechatPayOrder::class); //, 'id', 'wechat_pay_order_id'
    }

    public function getPayLink()
    {
        return config('app.url').'/wxpay/'.$this->id;
    }

    public function toogleActive()
    {
        $this->active = ! $this->active;
        $this->save();
    }
}
