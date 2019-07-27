<?php

namespace App\Models;

// use Actuallymab\LaravelComment\Commentable;
// use Actuallymab\LaravelComment\Models\Comment as LaravelComment;
use Carbon\Carbon;

class Comment // extends LaravelComment
{
    // use Commentable;
    protected $canBeRated = true;
    protected $mustBeApproved = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'commented_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'commentable_id');
    }

    //N minutes ago
    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('m/d H:i');
        // return Carbon::parse($date)->diffForHumans();
    }
}
