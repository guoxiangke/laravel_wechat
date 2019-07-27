@extends('layouts.wexin')
@section('title', '支付中...')

@section('content')
    <style>
        .wx-donate{
            text-align: center;
        }
        .wx-donate img {
            max-width: 300px;
        }
        #payit{
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

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="wx-donate">

                            <span id="payit" class="weui_btn weui_btn_primary">微信支付...</span>
                            <p>若3秒后无反应，请点击⬆️按钮！</p>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="//res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script>
    function callpay() {
        wx.chooseWXPay({
            timestamp: '{{ $config['timestamp'] }}',
            nonceStr: '{{ $config['nonceStr'] }}',
            package: '{{ $config['package'] }}',
            signType: '{{ $config['signType'] }}',
            paySign: '{{ $config['paySign'] }}',
            success: function (res) {
                // 支付成功后的回调函数
                // alert('永不止息，感恩有你!');
                window.location = '/thanks?id='+{{$orderId}};
            },
            fail: function (res) {
                alert('出错啦,请截图发给小永!永不止息，感恩有你!');
                alert(res);
            }
        });
    }
    window.onload = function () {
        $('#payit').click(function(e){
            e.preventDefault();
            callpay();
        });

        $(document).ready(function () {
            var jsConfig = '{!! $jsConfig !!}';
            wx.config(JSON.parse(jsConfig));
            setTimeout("callpay()",3000);
        });
    }
    </script>

@endsection
