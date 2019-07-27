@extends('layouts.wexin')
@section('title', '支付成功')

@section('styles')
    <style type="text/css">

        .wx-donate{
            text-align: center;
        }
        .wx-donate img {
            max-width: 300px;
        }
        #closeit{
            margin-top: 10%;
            margin-left: 10px;
            margin-right: 10px;
        }
        .weui_btn_primary {
            background-color: #04BE02;
        }
        .weui_btn {
            position: relative;
            display: block;
            margin-left: auto;
            margin-right: auto;
            padding-left: 14px;
            padding-right: 14px;
            box-sizing: border-box;
            font-size: 18px;
            text-align: center;
            text-decoration: none;
            color: #FFFFFF;
            line-height: 2.33333333;
            border-radius: 5px;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="page-foucs">
        <div class="row">
            <div class="col-md-12">
                <div class="text-center mt-2 wx-donate">
                    <span id="closeit" class="weui_btn weui_btn_primary">支付成功! 点此关闭</span>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="//res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script>
    window.onload = function () {
        $('#closeit').click(function(e){
            window.close();
            WeixinJSBridge.call('closeWindow');
        });

        $(document).ready(function () {
            window.close();
            WeixinJSBridge.call('closeWindow');
        });
    }
    </script>
@endsection
