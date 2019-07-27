@extends('layout')
@section('title', '我的推荐')

@section('content')
  <h1 class="mt-4">我的推荐</h1>
  <p>{{ $msg2 }}</p>
  <ol class="recommends">
  @foreach ($users as $user)
    @if($profile = $user->profile)
    <li class="media mb-4 {{$user->subscribe==1?"active":"inactive"}}" id="profile-{{ $user->id }}">
        <img class="mr-3 avatar" src="{{$profile->headimgurl}}" alt="image">
        <div class="media-body">
            <p class="mt-0">
              助力好友: {{ $profile->nickname }} <br/>
              助力时间: {{ $user->updated_at }}
              @if($user->subscribe!=1)
              <br/>助力无效: 已取关!
              @endif
            </p>
        </div>
    </li>
    @endif
  @endforeach
  </ol>
  <p>{{ $msg1 }}</p>
  {{ $users->links() }}
@endsection

