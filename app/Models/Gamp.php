<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gamp extends Model
{
    protected $fillable = ['client_id','category','action','label'];
}
