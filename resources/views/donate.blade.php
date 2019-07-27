@extends('layouts.wexin')

@section('title', '赞赏')
@section('content')
  <div class="wx-donate" id="app">
    <img src="{{ asset('/images/WechatIMG2277.jpg') }}" alt="">
    <br>
    <form method="POST" class="form-control" action="/donate" id="donate">
      @csrf
      <div class="numbers">
        <label for="input">赞赏金额</label>
        <br><br>
        <button class="wxpay_link" data-value="49" >49元</button>
        <button class="wxpay_link" data-value="99" >99元</button>
        <button class="wxpay_link" data-value="199">199元</button>
      </div>
      <br>
      <label for="input">其他金额</label>
      <br><br>
      <input type="number" min="1" id="fee" name="fee" placeholder="随机金额" step="10">
      <br><br>
      <button class="btn btn-primary weui_btn weui_btn_primary wxpay" id="submitForm">确定支付</button>
    </form>
    <br>
    <div class="others">
      <h3><a href="{{ asset('/images/WechatIMG229.png') }}">匿名/使用信用卡</a></h3> &nbsp;&nbsp;&nbsp;&nbsp;
      <h3><a href="{{ asset('/images/WechatIMG422.png') }} ">使用支付宝</a></h3>
    </div>
  </div>
@endsection


@section('scripts')
@endsection

@section('styles')

  <style>
  label{
    color:#666;
  }
  .others{
    display: inline-flex;
  }
  .wx-donate{
    text-align: center;
  }
  .wx-donate img {
    max-width: 200px;
  }
  #payit{
    margin-top: 60%;
    margin-left: 10px;
    margin-right: 10px;
  }
  .weui_btn_primary {
    background-color: #19be6b;
  }
  .wxpay{
  width: 70%;
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
    border-radius: 4px;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    overflow: hidden;
    max-width: 80%;
    border: none;
  }
  #fee{
    text-align: center;
    width: 30%;
    height: 32px;
    line-height: 1.5;
    padding: 4px 7px;
    font-size: 16px;
    border: 1px solid #dcdee2;
    border-radius: 4px;
    color: #515a6e;
    background-color: #fff;
    background-image: none;
    position: relative;
    cursor: text;
    transition: border .2s ease-in-out,background .2s ease-in-out,box-shadow .2s ease-in-out;
    font-family: 'Times New Roman';
  }
  .wxpay_link{
    cursor: pointer;
    display: inline-flex;
    font-size:16px;
    background-color:#19be6b;
    color:#FFFFFF;
    padding: 10px 20px;
    text-align:center;
    border: none;
    border-radius: 3px;
  }
  body{
    font-family: 'Times New Roman';
    background-color:#fff;
  }
  p,label{
    font-size:12px;
  }
  h3 a{
    font-size:12px;
    color:#999;
  }
  a
  </style>
@endsection
