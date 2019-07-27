<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Silvanite\Brandenburg\Traits\ValidatesPermissions;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    use ValidatesPermissions;
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\Post::class => \App\Policies\PostPolicy::class,
        \App\Models\AlbumSubscription::class => \App\Policies\AlbumSubscriptionPolicy::class,
        \App\Models\WechatAccount::class => \App\Policies\WechatAccountPolicy::class,
        \App\Models\WechatAutoReply::class => \App\Policies\WechatAutoReplyPolicy::class,
        \App\Models\WechatPayOrder::class => \App\Policies\WechatPayOrderPolicy::class,
        \App\Models\LyMeta::class => \App\Policies\LyMetaPolicy::class,
        \App\Models\LyLts::class => \App\Policies\LyLtsPolicy::class,

        \App\Models\Plan::class => \App\Policies\PlanPolicy::class,
        \App\Models\PlanSubscription::class => \App\Policies\PlanSubscriptionPolicy::class,
        \App\Models\Page::class => \App\Policies\PagePolicy::class,
        \App\Models\Album::class => \App\Policies\AlbumPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        collect([
            'viewPost',
            'managePost',
            'viewAlbumSubscription',
            'manageAlbumSubscription',
            'viewWechatAccount',
            'manageWechatAccount',
            'viewWechatAutoReply',
            'manageWechatAutoReply',
            'viewWechatPayOrder',
            'manageWechatPayOrder',
            'viewLyMeta',
            'manageLyMeta',
            'viewLyLts',
            'manageLyLts',
            'viewPlan',
            'managePlan',
            'viewPlanSubscription',
            'managePlanSubscription',
            'viewPage',
            'managePage',
            'viewAlbum',
            'manageAlbum',
        ])->each(function ($permission) {
            Gate::define($permission, function ($user) use ($permission) {
                if ($this->nobodyHasAccess($permission)) {
                    return true;
                }

                return $user->hasRoleWithPermission($permission);
            });
        });
        $this->registerPolicies();
    }
}
