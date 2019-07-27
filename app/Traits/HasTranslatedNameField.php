<?php

namespace App\Traits;

trait HasTranslatedNameField
{
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = json_encode(['en'=>$value]);
    }

    public function getNameAttribute($value)
    {
        return json_decode($value, 1)['en'];
    }
}
