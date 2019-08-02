<?php

namespace App\Models;

use App\Traits\HasTranslatedDescriptionField;
use App\Traits\HasTranslatedNameField;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasTranslatedNameField;
    use HasTranslatedDescriptionField;
    protected $casts = [
      'updated_at' => 'datetime',
  ];
}
