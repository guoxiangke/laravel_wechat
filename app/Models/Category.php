<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasTranslatedNameField;
use App\Traits\HasTranslatedDescriptionField;

class Category extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'slug',
        'name',
        'description',
        'parent_id'
    ];

    // protected $casts = [
    //     'name' => 'array',
    //     'description' => 'array',
    // ];

    use HasTranslatedNameField;
    use HasTranslatedDescriptionField;

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }


}
