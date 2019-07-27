<!DOCTYPE html>
<html>
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-129889764-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-129889764-1');
    </script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
      @if(isset($title)) {{ $title }}
      @else
        @yield('title')
      @endif
      | {{ config('app.name', 'Laravel') }}
    </title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- import Vue.js -->
    <!-- <script src="//cdn.bootcss.com/vue/2.5.16/vue.min.js"></script> -->
    <!-- import stylesheet -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/iview/3.0.1/styles/iview.css">
    <!-- import iView -->
    <!-- <script src="//cdnjs.cloudflare.com/ajax/libs/iview/3.0.1/iview.min.js"></script> -->

    <script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>

    <link rel="stylesheet" type="text/css" href="//res.wx.qq.com/open/libs/weui/1.1.3/weui.min.css">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body>
  <div class="container " id="app">
    <div class="full-height">
        @yield('content')
    </div>
    @include('layouts.footer')
  </div>
  @yield('scripts')
</body>
</html>
