<?php

namespace App\Traits;

use App\Models\LyMeta;
use App\Models\LyLts;
use App\Models\Album;

trait HasMorphsTargetField
{
    // return $user->hasOne('App\Phone');
    //会自动假设 Phone 模型有一个 user_id 外键
    public function lymeta()
    {
        return $this->hasOne(LyMeta::class, 'id', 'target_id');
    }

    public function lylts()
    {
        return $this->hasOne(LyLts::class, 'id', 'target_id');
    }

    public function album()
    {
        return $this->hasOne(Album::class, 'id', 'target_id');
    }
}
