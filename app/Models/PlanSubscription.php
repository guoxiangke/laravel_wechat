<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscription extends Model
{
    // protected $table = 'plan_subscriptions';

    protected $casts = [
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * wechatProfile.
     */
    public function profile()
    {
        return $this->hasOne(WechatUserProfile::class, 'user_id', 'user_id');
    }
}
