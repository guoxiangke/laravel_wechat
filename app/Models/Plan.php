<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslatedNameField;
use App\Traits\HasTranslatedDescriptionField;

class Plan extends Model
{

  use HasTranslatedNameField;
  use HasTranslatedDescriptionField;
  protected $casts = [
      'updated_at' => 'datetime'
  ];
}
