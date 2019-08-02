<?php

namespace App\Models;

// use Actuallymab\LaravelComment\CanComment;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
// use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use Overtrue\LaravelFollow\Traits\CanBeFollowed;
use Overtrue\LaravelFollow\Traits\CanBookmark;
use Overtrue\LaravelFollow\Traits\CanFollow;
use Overtrue\LaravelFollow\Traits\CanLike;
//use Overtrue\LaravelFollow\Traits\CanSubscribe;
use Overtrue\LaravelFollow\Traits\CanVote;
use Rinvex\Subscriptions\Traits\HasSubscriptions;
use Silvanite\Brandenburg\Role;
use Silvanite\Brandenburg\Traits\HasRoles;
use Trexology\Pointable\Contracts\Pointable;
use Trexology\Pointable\Traits\Pointable as PointableTrait;

class User extends Authenticatable implements Pointable
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    use Notifiable,
        // CanComment,
        HasRoles,
        CanLike, //todo 收听打卡
        CanVote,
        CanBookmark, //todo 收藏节目
        CanFollow,//todo only for seeker role.
        PointableTrait,
        CanBeFollowed;
    use HasSubscriptions;

    const POINT_COMMENT_DAY_LIMIT = 500;
    const POINT_MUSIC_DAY_LIMIT = 100;
    const POINT_PRE_COMMENT = 50;
    const POINT_PRE_MUSIC = 10;
    const POINT_PRE_USER_RECOMMEND = 1000; //1元
    const DEFAULT_ROLE = 'wx';
    const MP_ROLE = 'mp';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
        'subscribe',
        'telphone',
        'user_id',
        'isAdmin',
    ];

    //todo check by role
    //isAdmin is for comment no need approve
    public function isAdmin()
    {
        return true;
    }

    // function getNameAttribute($value)
    // {
    //     if ($this->exists && $this->profile) {
    //          return $this->profile->first(function ($item) {
    //             return ! empty($item->nickname);
    //         })->nickname;
    //     }
    //     return $value;
    // }

    /**
     * for Horizon::auth.
     *
     * @return bool [description]
     */
    public function isSuperuser()
    {
        return $this->id == 1;
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // public function posts()
    // {
    //     return $this->hasMany('App\Models\Post');
    // }

    /**
     * wechatProfile.
     */
    public function profile()
    {
        return $this->hasOne(WechatUserProfile::class);
    }

    /**
     * wechat account profile.
     */
    public function ghprofile()
    {
        return $this->hasOne(WechatAccountProfile::class, 'to_user_name', 'name');
    }

    public function subscriptions()
    {
        return $this->hasMany(AlbumSubscription::class);
    }

    public function get_free_subscription_counts()
    {
        return AlbumSubscription::where('user_id', $this->id)
            ->where('price', 0)
            ->where('active', 1)
            ->count();
    }

    //todo： 对不起，您当前的会员等级只可以订阅1个节目。想要更多会员特权，参考会员服务等级。
    //$subscribeCounts = AlbumSubscription::where('user_id', $this->user->album_subscriptions()->count());

    public function messages()
    {
        return $this->hasMany(WechatMessage::class, 'from_user_name', 'name');
    }

    public function toggleSubscribe()
    {
        $this->subscribe = !$this->subscribe;
        $this->save();
    }

    //
    public function recommenders()
    {
        return $this->hasMany(self::class);
    }

    //推荐者的 profile 信息
    public function recommender()
    {
        return $this->hasOne(WechatUserProfile::class, 'user_id', 'user_id');
        // return $this->hasOne(self::class, 'id', 'user_id');
    }

    //推荐的总数
    public function count_recommenders()
    {
        return $this->recommenders()->count();
    }

    //有效推荐的总数
    public function count_value_recommenders($byMonth = false)
    {
        $start = 0;
        if ($byMonth) {
            $start = Carbon::now()->startOfMonth()->toDateTimeString();
        }

        return $this->recommenders()
            ->where('subscribe', 1)
            ->where('created_at', '>', $start)
            ->count();
    }

    /**
     * [saveUser with role wx or mp].
     *
     * @param [type] $userName    [description]
     * @param [type] $role        [description]
     * @param int    $recommendId [description]
     *
     * @return [type] [description]
     */
    public static function newUser($userName, $role = self::DEFAULT_ROLE, $recommendId = 0)
    {
        $user = self::firstOrNew(
            [
                'name'  => $userName,
                'email' => $userName.'@'.$role,
            ]
        );
        if (!$user->id) {
            $password = Hash::make(str_random(6));
            $user->password = $password;
            // $user->remember_token = str_random(32);
            $user->save();
        }
        if ($role == self::DEFAULT_ROLE) {
            if ($user->user_id != $recommendId) {
                $user->user_id = $recommendId;
                $user->save();
            }
        }
        if ($role == self::MP_ROLE) {
            $user->user_id = 0;
            $user->subscribe = 0;
            $user->save();
        }
        $currentRoles = $user->roles()->get()->pluck('slug')->toArray();
        if (!in_array($role, $currentRoles)) {
            $user->assignRole($role);
        }

        return $user;
    }
}
