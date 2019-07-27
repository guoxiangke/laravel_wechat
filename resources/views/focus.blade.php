@extends('layout')
@section('title', '欢迎关注...')

@section('styles')
    <style type="text/css">
        #img img{
            max-width: 100%;
        }
    </style>
@endsection

@section('content')
    <div class="page-foucs">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div id="img">
                            <img src="/images/qrcode.jpg">
                        </div>
                        <div class="text-center mt-2">
                            <span class="text">长按关注</span>
                            <span class="text"><a href="/docs">常见问题/使用帮助</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
@endsection
