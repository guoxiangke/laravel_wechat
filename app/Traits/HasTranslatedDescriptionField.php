<?php

namespace App\Traits;

trait HasTranslatedDescriptionField
{
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = json_encode(['en'=>$value]);
    }

    public function getDescriptionAttribute($value)
    {
        return json_decode($value, 1)['en'];
    }
}
