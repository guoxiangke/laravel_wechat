@extends('layout')
@section('title', '推荐助力排行')

@section('content')
  <h1 class="mt-4">推荐助力排行榜{{$top}}</h1>
  <ol>
  @foreach ($profiles as $key => $profile)
    <li class="media mb-4" id="profile-{{ $profile->id }}">
        <img class="mr-3 avatar" src="{{$profile->headimgurl}}" alt="Generic placeholder image">
        <div class="media-body">
            <p class="mt-0" data-count="{{$uidCounts[$key]}}">宣传大使: {{ $profile->nickname }} <br/>
            <a href="/user/recommend/{{ $profile->user_id }}">影响力: {{ $uidCounts[$key] }} 人</a></p>
        </div>
    </li>
  @endforeach
  </ol>
@endsection
