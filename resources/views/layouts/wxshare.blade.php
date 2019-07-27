<script type="text/javascript">
  wx.config({
    debug: false,
    appId:  '{{$signPackage['appId']}}',
    timestamp:  '{{$signPackage['timestamp']}}',
    nonceStr:  '{{$signPackage['nonceStr']}}',
    signature:  '{{$signPackage['signature']}}',
    jsApiList: [
      'updateTimelineShareData',
      'updateAppMessageShareData',
      'onMenuShareAppMessage',
      'onMenuShareTimeline',
      'onMenuShareQQ',
      'onMenuShareWeibo',
      'onMenuShareQZone',
      'translateVoice',
      'getNetworkType',
      'hideOptionMenu',
      'showOptionMenu',
      'hideMenuItems',
      'showMenuItems',
    ]
  });

  wx.ready(function () {
    wx.onMenuShareTimeline({
      title: '{{$shareData['title']}}|{{ config('app.name') }}',
      link: '{{$shareData['link']}}',
      imgUrl: '{{$shareData['imgUrl']}}',
      success: function () {
        // alert('恭喜您获🉐️1积分');
      },
      cancel: function () {
         alert('分享是一种美德～');
      }
    });
    wx.onMenuShareAppMessage({
      title: '{{$shareData['title']}}|{{ config('app.name') }}',
      desc: '朋友，分享给您一篇我喜欢的内容', // 分享描述
      link: '{{$shareData['link']}}',
      imgUrl: '{{$shareData['imgUrl']}}',
      success: function () {
        // 设置成功
      },
      cancel: function () {
         alert('分享是一种美德');
      }
    })

  });
</script>
