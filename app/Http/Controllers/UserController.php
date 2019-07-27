<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\User;
use App\Models\WechatUserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //获取用户分享的用户
    public function recommend()
    {
        $user = Auth::user();
        $countAll = $user->count_recommenders();
        $countVaule = $user->count_value_recommenders();
        if (!$countAll) {
            $msg1 = '啊呜,您还没有成功分享!';
        } else {
            $msg1 = "迄今为止,您总共推荐了{$countAll}人!";
        }
        if ($countVaule) {
            $msg1 .= " 其中{$countVaule}人为有效关注!";
        }
        $monthVaule = $user->count_value_recommenders(1);
        if ($monthVaule) {
            $msg2 = '本次活动(/本月)您推荐了'.$monthVaule.'个有效用户.';
        } else {
            $msg2 = '本次活动(/本月)您推荐了0个有效用户.';
        }

        // select * from profiles as p where p.user_id in
        //     (select id form users where user_id = currentUid and subscribe=1)
        $users = $user->recommenders()
            ->with('profile')
            ->orderBy('updated_at', 'DESC')
            ->simplePaginate(15);

        return view('users.recommend', compact('msg1', 'msg2', 'users'));
    }

    public function recommendsTop($top = 100)
    {
        $account = Auth::user();
        $canManageUsers = $account->hasRoleWithPermission('manageUsers');
        if (!$canManageUsers) {
            return redirect('/')->with('status', 'Not Authorized!');
        }
        // $sql = "select user_id, count(*) as count from users where subscribe=1 group by user_id order by count";
        $recommenderIds = DB::table('users')->select(DB::raw('count(*) as count, user_id'))->where('subscribe', 1)->groupBy('user_id')->limit($top)->get(); //->where('user_id', '<>', 1)

        $uidCounts = $recommenderIds->pluck('count')->toArray();
        // dd($profiles->pluck('user_id')->unique());
        // bug, 保证一个用户只有一个 profile // user_id = 2345 has 2 profles!!!
        // https://www.kancloud.cn/php-jdxia/laravel5note/388603
        // 如果有多条，则修改第一条（但是为什么会有多条，因为create太多了？）
        // $user->avatar()->delete();
        // 删除找到的第一条 WechatUserProfile::where('user_id','2345')->first()->delete();
        $profiles = WechatUserProfile::whereIn('user_id', $recommenderIds->pluck('user_id')->toArray())
            ->limit($top)->get();

        return view('users.recommends_top', compact('top', 'uidCounts', 'profiles'));
    }

    //获取用户分享的用户 管理员
    public function recommendsBy(User $user)
    {
        $account = Auth::user();
        $canManageUsers = $account->hasRoleWithPermission('manageUsers');
        if (!$canManageUsers) {
            return redirect('/')->with('status', 'Not Authorized!');
        }

        $countAll = $user->count_recommenders();
        $countVaule = $user->count_value_recommenders();
        if (!$countAll) {
            $msg1 = '啊呜,Ta还没有成功分享!';
        }
        if ($countAll - $countVaule > 0) {
            $msg1 = "迄今Ta推荐了{$countAll}人,其中{$countVaule}人为有效关注!";
            $msg2 = '本次活动Ta推荐了'.$user->count_value_recommenders(1).'个有效用户.';
        } else {
            $msg1 = "Ta推荐了{$countAll}人!";
            $msg2 = '本次活动Ta推荐了0个有效用户.';
        }

        $users = $user->recommenders()
            ->with('profile')
            ->orderBy('updated_at', 'DESC')
            ->simplePaginate(15);

        return view('users.recommend', compact('msg1', 'msg2', 'users'));
    }

    public function subscription()
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions()
            ->with('album')
            ->where('target_type', Album::class)
            ->orderBy('price')
            ->orderBy('updated_at', 'DESC')
            ->simplePaginate(10);

        return view('users.subscription', compact('subscriptions'));
    }
}
