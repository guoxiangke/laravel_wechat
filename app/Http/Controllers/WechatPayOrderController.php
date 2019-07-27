<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\WechatPayOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use EasyWeChat;
use App\Services\Wechat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class WechatPayOrderController extends Controller
{
    public function show (WechatPayOrder $order) {
        $url = request()->getUri();
        $orderFields =['body','out_trade_no','total_fee','trade_type','openid'];
        $orderData   = array_filter($order->toArray(), function($v, $k) use ($orderFields) {
            return in_array($k, $orderFields);
        }, ARRAY_FILTER_USE_BOTH);
        $user = Auth::user();
        $orderData['openid'] = $user->name;
        $orderData['total_fee'] *= 100;
        // $payment = Factory::payment($config);
        $app = Wechat::init(1);
        $jsApiList = Wechat::jsApiList;
        $jsConfig = $app->jssdk
            ->setUrl($url)
            ->buildConfig($jsApiList);//,true,true

        /* @var $payment \EasyWeChat\Payment\Application */
        $app = EasyWeChat::payment(); // 微信支付
        //prepay_id有效时间：2小时
        $prepayId = $order->prepay_id;
        if(!$prepayId || Carbon::now()->diffInMinutes($order->updated_at)>=120){
            $result = $app->order->unify($orderData);
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                $prepayId = $result['prepay_id'];
                //todo  如果返回没有$prepayId，而是使用out_trade_no，可以放到队列里
                $order->prepay_id = $prepayId;
                $order->save();
                //$json = $jssdk->bridgeConfig($prepayId); // 返回 json 字符串，如果想返回数组，传第二个参数 false
            }else{
                dd($result,$orderData,$prepayId,$order->toArray());
            }
        }

        $config = $app->jssdk->sdkConfig($prepayId); // 返回数组

        $orderId = $order->id;

        return view('wxpay',compact('config','jsConfig','orderId'));//->with('config',$config);

    }

    public function wxpay_notify () {
        /* @var $app \EasyWeChat\Payment\Application */
        $app = EasyWeChat::payment(); // 微信支付
        $response = $app->handlePaidNotify(function ($message, $fail) {
            $success =  ($message['result_code']=="SUCCESS" && $message['return_code']=="SUCCESS")?:false;
            if(!$success) {
                Log::error(__LINE__,[__FUNCTION__,__CLASS__,'Order not Success.']);
                //$fail('Order not Success.');
                return true;
            }
            $order = WechatPayOrder::where('out_trade_no', $message['out_trade_no'])->first();
            if(!$order) {
                Log::error(__LINE__,[__FUNCTION__,__CLASS__,'Order not Exsits.']);
                //$fail('Order not Exsits.');
                return true;
            }
            $order->success = $success;
            $order->transaction_id = $message['transaction_id'];
            $order->bank_type = $message['bank_type'];
            $order->save();
            //todo notification wechat message tpl
            return true;
        });

        return $response;
    }

    public function donate(){
        //cookie RedirectAfterlogin
        return view('donate');
    }

    public function create(Request $request){
        if ($request->isMethod('post')) {
            $fee = $request->input('fee', 1);
            $user = Auth::user();
            $outTradeNo = config('wechat.payment.default.mch_id') .'|'. date('YmdHis') .'|'. $user->id; //32  mch_id|20180917103315|1
            //$openId = $user->name;
            $order = WechatPayOrder::Create([
                'user_id'   => $user->id,
                'target_type' => Page::class,
                'target_id'  => 1,
                'body'  => '赞助支持',//.$requestUri,
                'out_trade_no'  => $outTradeNo,
                'total_fee' => $fee,
                'trade_type' => 'JSAPI'
            ]);

            return redirect()->route('order', ['id' => $order->id]);
            //return $this->show($order);
            //return view('donate')->with('order', $order);
        }
    }
}
