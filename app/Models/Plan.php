<?php

namespace App\Models;

use App\Traits\HasTranslatedNameField;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslatedDescriptionField;

class Plan extends Model
{
    use HasTranslatedNameField;
    use HasTranslatedDescriptionField;
    protected $casts = [
      'updated_at' => 'datetime',
  ];
}
