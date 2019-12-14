<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/dev', function () {
    App\Services\Helper::dev();
    // $user = App\Models\User::find(1);
    // $plan = app('rinvex.subscriptions.plan')->find(2);
    // $user->newSubscription('main', $plan);
    // dd($plan->trial_interval, [$plan->trial_period,$plan->invoice_interval]);
    // dd($plan->toArray());
});

Route::get('/MP_verify_TneROHDiBDphZRvS.txt', function () {
    return 'TneROHDiBDphZRvS';
});

Route::any('/wechat/{toUserName}', 'WechatController@serve');

Route::group(['middleware' => ['web', 'wechat.oauth']], function () {
    Route::get('/user/login/wechat', 'WechatController@login')
    ->name('login');
});

Route::post('/wxpay/notify', 'WechatPayOrderController@wxpay_notify');
Route::group(['middleware' => ['auth']], function () {
    Route::get('/donate', 'WechatPayOrderController@donate');
    Route::post('/donate', 'WechatPayOrderController@create');
    Route::get('/wxpay/{order}', 'WechatPayOrderController@show')
        ->where('order', '[0-9]+')
        ->name('order');
    Route::get('/user/recommend', 'UserController@recommend')->name('user.recommend');
    Route::get('/user/recommend/top', 'UserController@recommendsTop')->name('user.recommend.top');
    Route::get('/user/recommend/top/{top}', 'UserController@recommendsTop')->name('user.recommend.tops');
    Route::get('/user/recommend/{user}', 'UserController@recommendsBy')->name('user.recommend.by');

    Route::get('/user/subscription', 'UserController@subscription')->name('user.subscription');
    Route::get('/user/subscription/{user}', 'UserController@recommendsBy')->name('user.subscription.by');

    Route::post('/editor/image/upload', 'FroalaController@imageUpload');
    Route::post('/editor/image/delete', 'FroalaController@imageDelete');
    Route::post('/editor/image/load', 'FroalaController@imageLoad');

    // Route::get('/LyAudio/{audio}', 'Api\LyAudioController@show')->name('lyaudio.show');
    //admin forms
    //自动发送信息给指定的用户!
    //

    // Route::get('/posts','PostsController@index');
    Route::get('posts/{slug}', 'PostController@showSlug')->name('Post.show');
    Route::get('LyAudio/{slug}', 'Api\LyAudioController@showSlug')->name('LyAudio.show');
});
Route::get('/ly', 'Api\LyMetaController@index')->name('lymeta.index');
Route::get('/lyapi/cc', 'Api\LyMetaController@cc');
Route::resource('subscriptions', 'AlbumSubscriptionController', ['only' => ['edit', 'update']])
    ->middleware('auth');

Route::get('/home', 'HomeController@index')->name('home');

// Route::group(['prefix' => 'admin'], function () {
//     Voyager::routes();
// });

Route::get('/focus', function () {
    return view('focus');
})->name('focus');
Route::get('/thanks', function () {
    return view('thanks');
})->name('thanks');

Route::get('/', function () {
    return view('welcome');
});

// 0位当月，1因为上月
// 义工精选 https://wechat.dev/statics/actions/lyapi_audio/gh_d9da0774e6da/0
// 良朋益友 https://wechat.dev/statics/actions/lyapi_audio/gh_9ce01a064bb6/0
// 良院统计 https://wechat.dev/statics/actions/lts33/gh_d9da0774e6da/0
Route::get('/statics/actions/{category}/{clientId}/{byMonth?}', 'GampController@actions')
    ->where('byMonth', '[0-6]');
// 按分类category查看  // 0位当月，1因为上月
// 义工精选 https://wechat.dev/statics/category/gh_d9da0774e6da/0
// 良朋益友 https://wechat.dev/statics/category/gh_9ce01a064bb6/0
Route::get('/statics/category/{clientId}/{byMonth?}', 'GampController@category')
    ->where('byMonth', '[0-6]');
// Route::get('{any}', function(){
//     return view('welcome',['title'=>'test']);
// })->where('any', '.*');
