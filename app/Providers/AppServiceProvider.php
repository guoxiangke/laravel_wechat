<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\LyAudio;
use App\Models\Category;
use Laravel\Horizon\Horizon;
use App\Models\WechatPayOrder;
use App\Observers\PostObserver;
use App\Observers\LyAudioObserver;
use App\Observers\CategoryObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use App\Observers\WechatPayOrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('zh');
        if (Config::get('app.env') != 'development') {
            URL::forceScheme('https');
        }

        Horizon::auth(function ($request) {
            $user = $request->user();
            if ($user && $user->isSuperuser()) {
                return true;
            } else {
                return false;
            }
        });
        Category::observe(CategoryObserver::class);
        Post::observe(PostObserver::class);
        LyAudio::observe(LyAudioObserver::class);
        WechatPayOrder::observe(WechatPayOrderObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
