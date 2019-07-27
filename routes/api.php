<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/ly/{code}/{offset?}', 'Api\LyMetaController@get')
    ->where('code', '[A-Za-z0-9]+')
    ->where('offset', '[0-9]+');

Route::get('/lts/{index}/{offset?}', 'Api\LyLtsController@get')
    ->where('index', '[0-9]+')
    ->where('offset', '[0-9]+');

Route::get('/users', function () {
    return factory('App\Models\User', 10)->make();
});

Route::get('/ly/code/all', 'Api\LyMetaController@all');

Route::resources([
    'posts' => 'Api\PostController'
]);
// Route::get('/posts', 'PostController@index');//list
// Route::get('/posts/{id}', 'PostController@show');//list one
// Route::post('/posts', 'PostController@store');//create
// Route::put('/posts', 'PostController@store');//update
// Route::delete('/posts', 'PostController@destroy');//
