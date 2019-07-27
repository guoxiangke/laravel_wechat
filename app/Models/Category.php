<?php

namespace App\Models;

use App\Traits\HasTranslatedDescriptionField;
use App\Traits\HasTranslatedNameField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'slug',
        'name',
        'description',
        'parent_id',
    ];

    // protected $casts = [
    //     'name' => 'array',
    //     'description' => 'array',
    // ];

    use HasTranslatedNameField;
    use HasTranslatedDescriptionField;

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
