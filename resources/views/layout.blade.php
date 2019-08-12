<!DOCTYPE html>
<html>
<head>
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

    <!-- import stylesheet -->
    <!-- import iView -->

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
