@extends ('layout')

@section('title')
{{$title}}
@endsection

@section('content')
  <div data-id="post-{{$post->id}}" class="posts full-height pt-4">
    <h1 id="title">{{$title}}</h1>

    <div class="body">
      @if (isset($video['url']))
        <div class="video-warpper">
        <plyr
          :mp4="'{{$video['url']}}'"
          :crossorigin="'{{$video['crossorigin']}}'"
          :image_url="'{{$video['image_url']}}'"
          :vtt_en="'{{$video['vtt']['en']}}'"
          :vtt_cn="'{{$video['vtt']['cn']}}'"></plyr>

          <div id="viads" class="mt-4 hidden">
            <h6>赞助商</h6>
          </div>
          </div>
      @else
        <div class="head-image">
          <img src="{{$post->image_url}}">
        </div>
      @endif
      @if ($album)
      <div class="albumMeta">
        <span class="title">所属专辑: <span class="data">{{$album->title}}</span></span>
        <span class="total">专辑总量: <span class="data">{{$album->getPostCounts()}}</span></span>
        <span class="index">专辑编号: <span class="data">【{{$album->getIndex()}}】</span></span>
      </div>
      @endif
      @if (isset($audio['url']))
        <plyrmp3 :mp3="'{{$audio['url']}}'"></plyrmp3>
        <p class="text-center tips">iPhone后台播放:微信右上角-->[浮窗]即可</p>
      @endif
      <div class="body-p my-3">
        {!!$post->body!!}
      </div>
      <larecipe-back-to-top></larecipe-back-to-top>

      <div id="comments">
        <h5>{{__('Comments')}}</h5>
      </div>
    </div>

  </div>
@endsection
@section('scripts')
  @if ($signPackage)
    @include('layouts.wxshare', ['signPackage' => $signPackage,'shareData' => $shareData])
  @endif
@endsection
