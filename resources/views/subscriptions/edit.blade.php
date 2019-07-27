@extends('layouts.app')
@section('title', '订阅更新')

@section('content')
  <div id="app">
    <!-- will be used to show any messages -->
    @if (Session::has('message'))
        <div class="alert alert-info">{{ Session::get('message') }}</div>
    @endif


     {!! form($form) !!}
    {{-- <subscriptionUpdate></subscriptionUpdate> --}}
  </div>
@endsection
