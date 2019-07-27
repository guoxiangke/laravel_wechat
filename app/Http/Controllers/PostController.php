<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\Wechat;
use Illuminate\Http\Request;
// use App\Models\Comment;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $video = $post->get_video();
        $audio = $post->get_audio();

        $link = str_replace('http://', 'https://', url()->full());
        $signPackage = Wechat::getSignPackage($link);
        $imgUrl = $post->getImageUrl();
        $post->image_url = proximage($imgUrl)->width(960)->get();
        $title = $post->title;
        if ($album = $post->album()->first()) {
            $title = $post->title.'('.$post->getIndex().'/'.$album->getPostCounts().')';
        }

        $shareData = [
            'title' => $title,
            'link' => $link,
            'imgUrl' => $imgUrl,
        ];

        return view('posts.show', [
            'title' => $title,
            'post' => $post,
            'video' => $video,
            'audio' => $audio,
            'album' => $album,
            'shareData' => $shareData,
            'signPackage' => $signPackage,
        ]);
    }

    public function showSlug($slugString)
    {
        $post = Post::whereSlug($slugString)->firstOrFail();

        return $this->show($post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
