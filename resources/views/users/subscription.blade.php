@extends('layout')
@section('title', '我的订阅推送')

@section('content')
  <h1 class="mt-4">我的订阅推送</h1>
  <div class="recommends">
  @foreach ($subscriptions as $subscription)
    @php
      $album = $subscription->album;
      $activeClass = 'is_active';
      if(!$subscription->active){
        $activeClass = 'disabled';
      }
      $progress = $album->getPostCounts();
      if($subscription->count){
        $totalCount = $album->getPostCounts();
        $precent = $subscription->count / $progress * 100;
        $progress = $subscription->count . '/' . $progress;
        $precent .= "%";
        // dd($precent);
      }
    @endphp
    <div class=" mb-3" id="profile-{{ $subscription->id }}">
          <div class="s-poster box">
            <div class="album-title">
              <h5>{{ $album->title }}({{ $progress }})</h5>
            </div>
            <div class="ribbon ribbon-top-right"><span>原价: <s>¥  {{ $album->ori_price }} </s></span></div>
            @if($subscription->price<1 && $subscription->price>0)
            @endif
            <div class="image-album">
            <img src="https://images.weserv.nl/?w=450&url=https://wechat.yongbuzhixi.com/storage/{{$album->image}}">
            </div>
            <div class="meta">
              @if($subscription->price<0)
              <span><a target="_blank" href="{{$subscription->getPayLink()}}" class="weui-btn weui-btn_primary">立刻支付¥{{ -$subscription->price }}接收推送</a></span>
              @endif

            @if($subscription->count)
              @if(!$subscription->active)
              <span><a target="_blank" href="{{$subscription->getPayLink()}}" class="weui-btn weui-btn_plain-primary">点击激活,再次推送</a></span>
              @elseif($subscription->price>0&&$subscription->price<1)
              <span class="weui-btn weui-btn_default">已推荐{{ $subscription->price*100 }}人免费成交</span>
              @endif
              <div class="weui-progress" id="send-progress">
                <div class="weui-progress__bar">
                    <div class="weui-progress__inner-bar js_progress" style="width:{{$precent}};"></div>
                </div>
              </div>
            @endif
            </div>
          </div>
    </div>
  @endforeach
  </div>
  {{ $subscriptions->links() }}
@endsection

@section('styles')
<style type="text/css">
.image-album{
  overflow: hidden;
}
.meta .weui-btn{
  border:none;
  border-radius: 0;
  opacity: .7;
}
.meta .weui-btn:after{
  border:none;
  border-radius: 0;
}
.meta{
    position: absolute;
    bottom: 0;
    width: 100%;
    z-index: 9999;
}
.album-title{
  width: 100%;
  height: 100%;
  text-align: center;
  overflow: hidden;
  position: absolute;
}
.album-title h5{
  background: #000;
    opacity: 0.6;
    color: #fff;
    padding:10px;
}
.box {
  position: relative;
  background: #f0f0f0;
  box-shadow: 0 0 15px rgba(0,0,0,.1);
}
/* common */
.ribbon {
  width: 150px;
  height: 150px;
  overflow: hidden;
  position: absolute;
}
.ribbon::before,
.ribbon::after {
  position: absolute;
  z-index: -1;
  content: '';
  display: block;
  border: 5px solid #2980b9;
}
.ribbon span {
  position: absolute;
  display: block;
  width: 225px;
  padding: 15px 0;
  background-color: #3498db;
  box-shadow: 0 5px 10px rgba(0,0,0,.1);
  color: #fff;
  font: 700 18px/1 'Lato', sans-serif;
  text-shadow: 0 1px 1px rgba(0,0,0,.2);
  text-transform: uppercase;
  text-align: center;
}

/* top left*/
.ribbon-top-left {
  top: -10px;
  left: -10px;
}
.ribbon-top-left::before,
.ribbon-top-left::after {
  border-top-color: transparent;
  border-left-color: transparent;
}
.ribbon-top-left::before {
  top: 0;
  right: 0;
}
.ribbon-top-left::after {
  bottom: 0;
  left: 0;
}
.ribbon-top-left span {
  right: -25px;
  top: 30px;
  transform: rotate(-45deg);
}

/* top right*/
.ribbon-top-right {
  top: -10px;
  right: -10px;
}
.ribbon-top-right::before,
.ribbon-top-right::after {
  border-top-color: transparent;
  border-right-color: transparent;
}
.ribbon-top-right::before {
  top: 0;
  left: 0;
}
.ribbon-top-right::after {
  bottom: 0;
  right: 0;
}
.ribbon-top-right span {
  left: -25px;
  top: 30px;
  transform: rotate(45deg);
}

/* bottom left*/
.ribbon-bottom-left {
  bottom: -10px;
  left: -10px;
}
.ribbon-bottom-left::before,
.ribbon-bottom-left::after {
  border-bottom-color: transparent;
  border-left-color: transparent;
}
.ribbon-bottom-left::before {
  bottom: 0;
  right: 0;
}
.ribbon-bottom-left::after {
  top: 0;
  left: 0;
}
.ribbon-bottom-left span {
  right: -25px;
  bottom: 30px;
  transform: rotate(225deg);
}

/* bottom right*/
.ribbon-bottom-right {
  bottom: -10px;
  right: -10px;
}
.ribbon-bottom-right::before,
.ribbon-bottom-right::after {
  border-bottom-color: transparent;
  border-right-color: transparent;
}
.ribbon-bottom-right::before {
  bottom: 0;
  left: 0;
}
.ribbon-bottom-right::after {
  top: 0;
  right: 0;
}
.ribbon-bottom-right span {
  left: -25px;
  bottom: 30px;
  transform: rotate(-225deg);
}
</style>
@endsection
