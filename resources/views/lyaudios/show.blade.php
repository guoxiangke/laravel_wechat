@extends('layout')

@section('content')
<div class="row justify-content-center pt-4">
    <div class="col-md-12">
        <article class="main-content">
          <div class="card col-md-12 col-sm-10 col-lg-4" >
            <img class="card-img-top" src="{{ $audio->lymeta->image }}" alt="{{ $audio->lymeta->name }}">
            <div class="card-body">
              <h1 class="card-title" id="title">【{{$audio->lymeta->index}}】{{ $audio->lymeta->name }}-{{$audio->play_at}}</h1>
              <p class="card-text">{!! $audio->excerpt !!} ...</p>
              <p>关注永不止息-云彩助手，回复【{{$audio->lymeta->index}}】即可收听当日精彩节目！</p>

              <a href="#comments" class="btn btn-primary">查看精华评论</a>
            </div>
          </div>

          <div class="mt-4">
          @if($audioUrl)
            <plyrmp3 :mp3="'{{$audioUrl}}'"></plyrmp3>
            <p class="mt-1 text-center">iPhone后台播放:微信右上角->[浮窗]即可</p>
          @endif
            <div class="body">
            {!! $audio->body !!}
            </div>
            <larecipe-back-to-top></larecipe-back-to-top>
          </div>

        </article>
        <div id="comments">
          <h5>{{__('Comments')}}</h5>
            
        </div>
    </div>
</div>
@endsection

@section('scripts')
  @if ($signPackage)
    @include('layouts.wxshare', ['signPackage' => $signPackage,'shareData' => $shareData]);
  @endif
@endsection
